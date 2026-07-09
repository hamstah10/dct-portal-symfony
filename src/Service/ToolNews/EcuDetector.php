<?php

namespace App\Service\ToolNews;

use App\Entity\EcuDetectionPattern;
use App\Entity\ToolNewsItem;
use App\Repository\EcuDetectionPatternRepository;

/**
 * Analyzes ToolNewsItem text (title/excerpt/content/rawData) for mentions of
 * ECU manufacturers, ECU families/names, MCUs, read methods, protocols and
 * vehicle hints, using a fixed set of built-in regex rules plus optional
 * extra patterns maintained in EasyAdmin (EcuDetectionPattern).
 *
 * This is intentionally regex-based (no NLP/ML) for the MVP: it must work
 * without any DB patterns configured, and DB patterns only add to it.
 */
final class EcuDetector
{
    /**
     * Manufacturer display name => list of patterns (case-insensitive).
     * The first key is also the normalized value stored on the result.
     *
     * @var array<string, list<string>>
     */
    private const MANUFACTURER_PATTERNS = [
        'Bosch' => ['Bosch'],
        'Continental' => ['Continental', 'Siemens/Continental', 'Siemens'],
        'Delphi' => ['Delphi'],
        'Denso' => ['Denso'],
        'Marelli' => ['Marelli', 'Magneti Marelli'],
        'Temic' => ['Temic'],
        'ZF' => ['ZF'],
        'Getrag' => ['Getrag'],
        'Vitesco' => ['Vitesco'],
        'Keihin' => ['Keihin'],
        'Hitachi' => ['Hitachi'],
        'Transtron' => ['Transtron'],
        'VAG' => ['VAG', 'Volkswagen Group'],
        'Mercedes' => ['Mercedes-Benz', 'Mercedes'],
        'BMW' => ['BMW'],
    ];

    /**
     * ECU family name => regex fragment matching that family's typical name pattern.
     * Order matters: longer/more specific prefixes must be checked before shorter ones.
     *
     * @var array<string, string>
     */
    private const ECU_FAMILY_PATTERNS = [
        'MD1' => '\bMD1[A-Z0-9]{2,8}\b',
        'MG1' => '\bMG1[A-Z0-9]{2,8}\b',
        'EDC17' => '\bEDC17[A-Z0-9]{1,8}\b',
        'EDC16' => '\bEDC16[A-Z0-9]{1,8}\b',
        'EDC15' => '\bEDC15[A-Z0-9]{1,8}\b',
        'MED17' => '\bMED17[A-Z0-9.]{1,10}\b',
        'MED9' => '\bMED9[A-Z0-9.]{1,10}\b',
        'ME17' => '\bME17[A-Z0-9.]{1,10}\b',
        'ME7' => '\bME7[A-Z0-9.]{1,10}\b',
        'SID' => '\bSID[0-9A-Z.]{2,8}\b',
        'SIMOS' => '\bSIMOS[0-9A-Z.]{1,8}\b',
        'PCR' => '\bPCR[0-9A-Z.]{1,8}\b',
        'PPD' => '\bPPD[0-9A-Z.]{1,8}\b',
        'DCM' => '\bDCM[0-9A-Z.]{1,8}\b',
        'DDE' => '\bDDE[0-9A-Z.]{1,8}\b',
        'DME' => '\bDME[0-9A-Z.]{1,8}\b',
        'MEVD' => '\bMEVD[0-9A-Z.]{1,10}\b',
        'MSD' => '\bMSD[0-9A-Z.]{1,8}\b',
        'MJD' => '\bMJD[0-9A-Z.]{1,8}\b',
        'MJ8' => '\bMJ8[A-Z0-9.]{1,8}\b',
        'MJ9' => '\bMJ9[A-Z0-9.]{1,8}\b',
        'IAW' => '\bIAW[0-9A-Z.]{1,8}\b',
        'CPC' => '\bCPC[0-9A-Z.]{1,8}\b',
        'CRD' => '\bCRD[0-9A-Z.]{1,8}\b',
        'CDI' => '\bCDI[0-9A-Z.]{1,8}\b',
        'DQ' => '\bDQ[0-9]{3}\b',
        'DL' => '\bDL[0-9]{3}\b',
        'VL' => '\bVL[0-9]{3}\b',
        '8HP' => '\b8HP[0-9]{2,3}\b',
        '9HP' => '\b9HP[0-9]{2,3}\b',
        '9G' => '\b9G[- ]?Tronic\b',
        '7G' => '\b7G[- ]?Tronic\b',
    ];

    /**
     * Explicit ECU-name regex list (kept separate from family patterns so a
     * concrete name like "MD1CS069" can be captured verbatim as ecuName,
     * while the family "MD1" is derived from it).
     *
     * @var list<string>
     */
    private const ECU_NAME_PATTERNS = [
        '\bMD1[A-Z0-9]{2,8}\b',
        '\bMG1[A-Z0-9]{2,8}\b',
        '\bEDC17[A-Z0-9]{1,8}\b',
        '\bEDC16[A-Z0-9]{1,8}\b',
        '\bEDC15[A-Z0-9]{1,8}\b',
        '\bMED17[A-Z0-9.]{1,10}\b',
        '\bSID[0-9A-Z.]{2,8}\b',
        '\bDCM[0-9A-Z.]{1,8}\b',
        '\bPCR[0-9A-Z.]{1,8}\b',
        '\bSIMOS[0-9A-Z.]{1,8}\b',
        '\bMEVD[0-9A-Z.]{1,10}\b',
        '\bMSD[0-9A-Z.]{1,8}\b',
        '\bMJD[0-9A-Z.]{1,8}\b',
        '\bMJ[0-9][A-Z0-9.]{1,8}\b',
        '\bDQ[0-9]{3}\b',
        '\bDL[0-9]{3}\b',
        '\b8HP[0-9]{2,3}\b',
        '\b9HP[0-9]{2,3}\b',
    ];

    /**
     * @var list<string>
     */
    private const MCU_PATTERNS = [
        '\bTC17[0-9]{2}\b',
        '\bTC176[0-9]\b',
        '\bTC178[0-9]\b',
        '\bTC179[0-9]\b',
        '\bTC27[0-9]\b',
        '\bTC29[0-9]\b',
        '\bTC3[0-9]{2}\b',
        '\bSPC[0-9]{4,6}\b',
        '\bMPC[0-9]{4,6}\b',
        '\bRH850\b',
        '\bSH725[0-9]\b',
    ];

    /**
     * Read/write method display value => patterns. Order matters: more
     * specific phrases (e.g. "Bench Mode") must be listed before the
     * shorter form ("Bench") so normalization doesn't shadow it.
     *
     * @var array<string, list<string>>
     */
    private const READ_METHOD_PATTERNS = [
        'Bench' => ['Bench Mode', 'Bench'],
        'Boot' => ['Bootloader', 'Boot'],
        'OBD' => ['OBD'],
        'Service Mode' => ['Service Mode'],
        'BDM' => ['BDM'],
        'JTAG' => ['JTAG'],
        'Tricore' => ['Tricore'],
    ];

    /**
     * Protocol/type display value => patterns. Order matters for the same
     * reason as read methods (longer phrases first).
     *
     * @var array<string, list<string>>
     */
    private const PROTOCOL_PATTERNS = [
        'No RFT' => ['No RFT'],
        'RFT' => ['RFT'],
        'VR' => ['Virtual Read', 'VR'],
        'Checksum' => ['Checksums', 'Checksum'],
        'TCU' => ['Automatic Transmission', 'Transmission', 'Gearbox', 'TCU'],
        'DCT' => ['DCT'],
        'ECU' => ['ECU'],
    ];

    /**
     * Vehicle brand/model display value => patterns.
     *
     * @var array<string, list<string>>
     */
    private const VEHICLE_HINT_PATTERNS = [
        'Fiat Ducato' => ['Fiat Ducato'],
        'Ford Transit' => ['Ford Transit'],
        'Peugeot Boxer' => ['Peugeot Boxer'],
        'Citroën Jumper' => ['Citroën Jumper', 'Citroen Jumper'],
        'Opel Movano' => ['Opel Movano'],
        'Renault Master' => ['Renault Master'],
        'Iveco Daily' => ['Iveco Daily'],
        'Volvo Trucks' => ['Volvo Trucks'],
        'BMW' => ['BMW'],
        'Mercedes' => ['Mercedes-Benz', 'Mercedes'],
        'Audi' => ['Audi'],
        'Volkswagen' => ['Volkswagen', 'VW'],
        'Porsche' => ['Porsche'],
        'Fiat' => ['Fiat'],
        'Ford' => ['Ford'],
        'Peugeot' => ['Peugeot'],
        'Citroën' => ['Citroën', 'Citroen'],
        'Opel' => ['Opel'],
        'Renault' => ['Renault'],
        'Iveco' => ['Iveco'],
        'MAN' => ['MAN'],
        'Scania' => ['Scania'],
        'Volvo' => ['Volvo'],
        'DAF' => ['DAF'],
        'Caterpillar' => ['Caterpillar'],
        'John Deere' => ['John Deere'],
        'Fendt' => ['Fendt'],
        'Deutz' => ['Deutz'],
        'New Holland' => ['New Holland'],
    ];

    public function __construct(
        private readonly EcuDetectionPatternRepository $ecuDetectionPatternRepository,
    ) {
    }

    /**
     * @return list<array{
     *     manufacturer: ?string,
     *     ecuFamily: ?string,
     *     ecuName: ?string,
     *     mcu: ?string,
     *     readMethod: ?string,
     *     protocol: ?string,
     *     vehicleHint: ?string,
     *     confidence: float,
     *     rawText: string,
     *     matchData: array<string, mixed>
     * }>
     */
    public function analyzeNewsItem(ToolNewsItem $newsItem): array
    {
        $parts = array_filter([
            $newsItem->getTitle(),
            $newsItem->getExcerpt(),
            $newsItem->getContent(),
            $this->rawDataToText($newsItem->getRawData()),
        ]);

        return $this->analyzeText(implode("\n", $parts));
    }

    /**
     * @return list<array{
     *     manufacturer: ?string,
     *     ecuFamily: ?string,
     *     ecuName: ?string,
     *     mcu: ?string,
     *     readMethod: ?string,
     *     protocol: ?string,
     *     vehicleHint: ?string,
     *     confidence: float,
     *     rawText: string,
     *     matchData: array<string, mixed>
     * }>
     */
    public function analyzeText(string $text): array
    {
        $trimmed = trim($text);
        if ('' === $trimmed) {
            return [];
        }

        $manufacturer = $this->matchFirst($trimmed, self::MANUFACTURER_PATTERNS);
        $mcu = $this->matchFirstRegexList($trimmed, self::MCU_PATTERNS);
        $readMethod = $this->matchFirst($trimmed, self::READ_METHOD_PATTERNS);
        $protocol = $this->matchFirst($trimmed, self::PROTOCOL_PATTERNS);
        $vehicleHint = $this->matchFirst($trimmed, self::VEHICLE_HINT_PATTERNS);

        $ecuNames = $this->matchAllRegexList($trimmed, self::ECU_NAME_PATTERNS);
        $dbMatches = $this->matchDbPatterns($trimmed);

        $manufacturer = $manufacturer ?? $dbMatches[EcuDetectionPattern::TYPE_MANUFACTURER] ?? null;
        $mcu = $mcu ?? $dbMatches[EcuDetectionPattern::TYPE_MCU] ?? null;
        $readMethod = $readMethod ?? $dbMatches[EcuDetectionPattern::TYPE_READ_METHOD] ?? null;
        $protocol = $protocol ?? $dbMatches[EcuDetectionPattern::TYPE_PROTOCOL] ?? null;
        $vehicleHint = $vehicleHint ?? $dbMatches[EcuDetectionPattern::TYPE_VEHICLE_HINT] ?? null;

        if ([] === $ecuNames && isset($dbMatches[EcuDetectionPattern::TYPE_ECU_NAME])) {
            $ecuNames = [$dbMatches[EcuDetectionPattern::TYPE_ECU_NAME]];
        }

        $results = [];

        if ([] === $ecuNames) {
            $ecuFamily = $this->matchFamilyOnly($trimmed) ?? ($dbMatches[EcuDetectionPattern::TYPE_ECU_FAMILY] ?? null);

            if (null === $manufacturer && null === $ecuFamily && null === $mcu) {
                return [];
            }

            $results[] = $this->buildResult($manufacturer, $ecuFamily, null, $mcu, $readMethod, $protocol, $vehicleHint, $trimmed);

            return $results;
        }

        foreach (array_unique($ecuNames) as $ecuName) {
            $ecuFamily = $this->deriveFamilyFromName($ecuName);
            $results[] = $this->buildResult($manufacturer, $ecuFamily, $ecuName, $mcu, $readMethod, $protocol, $vehicleHint, $trimmed);
        }

        return $results;
    }

    /**
     * @return array{
     *     manufacturer: ?string,
     *     ecuFamily: ?string,
     *     ecuName: ?string,
     *     mcu: ?string,
     *     readMethod: ?string,
     *     protocol: ?string,
     *     vehicleHint: ?string,
     *     confidence: float,
     *     rawText: string,
     *     matchData: array<string, mixed>
     * }
     */
    private function buildResult(
        ?string $manufacturer,
        ?string $ecuFamily,
        ?string $ecuName,
        ?string $mcu,
        ?string $readMethod,
        ?string $protocol,
        ?string $vehicleHint,
        string $rawText,
    ): array {
        $confidence = 0.0;
        $confidence += null !== $manufacturer ? 0.25 : 0.0;
        $confidence += null !== $ecuName ? 0.25 : 0.0;
        $confidence += null !== $ecuFamily ? 0.15 : 0.0;
        $confidence += null !== $mcu ? 0.15 : 0.0;
        $confidence += null !== $readMethod ? 0.10 : 0.0;
        $confidence += null !== $protocol ? 0.05 : 0.0;
        $confidence += null !== $vehicleHint ? 0.05 : 0.0;
        $confidence = min(1.0, $confidence);

        return [
            'manufacturer' => $manufacturer,
            'ecuFamily' => $ecuFamily,
            'ecuName' => $ecuName,
            'mcu' => $mcu,
            'readMethod' => $readMethod,
            'protocol' => $protocol,
            'vehicleHint' => $vehicleHint,
            'confidence' => $confidence,
            'rawText' => $rawText,
            'matchData' => array_filter([
                'manufacturer' => $manufacturer,
                'ecuFamily' => $ecuFamily,
                'ecuName' => $ecuName,
                'mcu' => $mcu,
                'readMethod' => $readMethod,
                'protocol' => $protocol,
                'vehicleHint' => $vehicleHint,
            ], static fn (mixed $value): bool => null !== $value),
        ];
    }

    private function deriveFamilyFromName(string $ecuName): ?string
    {
        foreach (self::ECU_FAMILY_PATTERNS as $family => $pattern) {
            if (1 === preg_match('/^' . $pattern . '$/i', $ecuName)) {
                return $family;
            }
        }

        // fall back to prefix matching (e.g. "MD1CS069" starts with "MD1")
        foreach (array_keys(self::ECU_FAMILY_PATTERNS) as $family) {
            if (0 === stripos($ecuName, $family)) {
                return $family;
            }
        }

        return null;
    }

    private function matchFamilyOnly(string $text): ?string
    {
        foreach (self::ECU_FAMILY_PATTERNS as $family => $pattern) {
            if (1 === preg_match('/' . $pattern . '/i', $text)) {
                return $family;
            }
        }

        return null;
    }

    /**
     * @param array<string, list<string>> $patternMap
     */
    private function matchFirst(string $text, array $patternMap): ?string
    {
        foreach ($patternMap as $normalized => $patterns) {
            foreach ($patterns as $needle) {
                if (false !== stripos($text, $needle)) {
                    return $normalized;
                }
            }
        }

        return null;
    }

    /**
     * @param list<string> $regexList
     */
    private function matchFirstRegexList(string $text, array $regexList): ?string
    {
        foreach ($regexList as $pattern) {
            if (preg_match('/' . $pattern . '/i', $text, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * @param list<string> $regexList
     *
     * @return list<string>
     */
    private function matchAllRegexList(string $text, array $regexList): array
    {
        $found = [];
        foreach ($regexList as $pattern) {
            if (preg_match_all('/' . $pattern . '/i', $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $found[] = strtoupper($match);
                }
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * Applies any active EcuDetectionPattern rows on top of the built-in
     * rules. Returns the first match per type, using normalizedValue when
     * set, otherwise the raw matched text.
     *
     * @return array<string, string>
     */
    private function matchDbPatterns(string $text): array
    {
        $patterns = $this->ecuDetectionPatternRepository->findAllActive();
        if ([] === $patterns) {
            return [];
        }

        $matchesByType = [];
        foreach ($patterns as $pattern) {
            $type = $pattern->getType();
            if (isset($matchesByType[$type])) {
                continue;
            }

            $needle = $pattern->getPattern();
            if (null === $needle || '' === $needle) {
                continue;
            }

            if ($pattern->isRegex()) {
                $regex = $this->normalizeRegexDelimiters($needle);
                if (false === @preg_match($regex, '')) {
                    continue;
                }

                if (preg_match($regex, $text, $matches)) {
                    $matchesByType[$type] = $pattern->getNormalizedValue() ?? ($matches[0] ?? $needle);
                }
            } elseif (false !== stripos($text, $needle)) {
                $matchesByType[$type] = $pattern->getNormalizedValue() ?? $needle;
            }
        }

        return $matchesByType;
    }

    private function normalizeRegexDelimiters(string $pattern): string
    {
        $trimmed = trim($pattern);
        if ('' !== $trimmed && '/' === $trimmed[0]) {
            return $trimmed;
        }

        return '/' . $trimmed . '/i';
    }

    private function rawDataToText(?array $rawData): string
    {
        if (null === $rawData || [] === $rawData) {
            return '';
        }

        $parts = [];
        array_walk_recursive($rawData, static function (mixed $value) use (&$parts): void {
            if (is_scalar($value)) {
                $parts[] = (string) $value;
            }
        });

        return implode(' ', $parts);
    }
}
