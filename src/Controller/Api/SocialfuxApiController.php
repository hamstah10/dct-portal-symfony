<?php

namespace App\Controller\Api;

use App\Entity\SocialfuxDesign;
use App\Entity\SocialfuxTemplate;
use App\Repository\SocialfuxDesignRepository;
use App\Repository\SocialfuxTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/socialfux')]
final class SocialfuxApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SocialfuxDesignRepository $designRepository,
        private readonly SocialfuxTemplateRepository $templateRepository,
        #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    // ── Designs ────────────────────────────────────────────────────────────

    #[Route('/designs', name: 'socialfux_designs_list', methods: ['GET'])]
    public function listDesigns(): JsonResponse
    {
        $designs = $this->designRepository->findBy(['isDeleted' => false], ['updatedAt' => 'DESC']);

        return $this->json(['designs' => array_map(fn (SocialfuxDesign $d) => $d->toArray(), $designs)]);
    }

    #[Route('/designs/{id}', name: 'socialfux_design_get', methods: ['GET'])]
    public function getDesign(string $id): JsonResponse
    {
        $design = $this->designRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$design) {
            throw new NotFoundHttpException('Design not found');
        }

        return $this->json($design->toArray());
    }

    #[Route('/designs', name: 'socialfux_design_create', methods: ['POST'])]
    public function createDesign(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $design = new SocialfuxDesign((string) Uuid::v4());
        $design->setName((string) ($data['name'] ?? 'Unbenannt'));
        $design->setCanvasPreset((string) ($data['canvasPreset'] ?? ''));
        $design->setCanvasWidth((int) ($data['canvasWidth'] ?? 1080));
        $design->setCanvasHeight((int) ($data['canvasHeight'] ?? 1080));
        $design->setCanvasBackground((string) ($data['canvasBackground'] ?? '#ffffff'));
        $design->setElements(is_array($data['elements'] ?? null) ? $data['elements'] : []);
        $design->setThumbnail(isset($data['thumbnail']) && is_string($data['thumbnail']) ? $data['thumbnail'] : null);

        $this->em->persist($design);
        $this->em->flush();

        return $this->json($design->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/designs/{id}', name: 'socialfux_design_update', methods: ['PUT', 'PATCH'])]
    public function updateDesign(string $id, Request $request): JsonResponse
    {
        $design = $this->designRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$design) {
            throw new NotFoundHttpException('Design not found');
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (isset($data['name'])) $design->setName((string) $data['name']);
        if (isset($data['canvasPreset'])) $design->setCanvasPreset((string) $data['canvasPreset']);
        if (isset($data['canvasWidth'])) $design->setCanvasWidth((int) $data['canvasWidth']);
        if (isset($data['canvasHeight'])) $design->setCanvasHeight((int) $data['canvasHeight']);
        if (isset($data['canvasBackground'])) $design->setCanvasBackground((string) $data['canvasBackground']);
        if (isset($data['elements']) && is_array($data['elements'])) $design->setElements($data['elements']);
        if (array_key_exists('thumbnail', $data)) $design->setThumbnail(is_string($data['thumbnail']) ? $data['thumbnail'] : null);

        $this->em->flush();

        return $this->json($design->toArray());
    }

    #[Route('/designs/{id}', name: 'socialfux_design_delete', methods: ['DELETE'])]
    public function deleteDesign(string $id): JsonResponse
    {
        $design = $this->designRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$design) {
            throw new NotFoundHttpException('Design not found');
        }

        $design->setIsDeleted(true);
        $this->em->flush();

        return $this->json(['message' => 'Design deleted']);
    }

    // ── Templates ──────────────────────────────────────────────────────────

    #[Route('/templates/categories', name: 'socialfux_template_categories', methods: ['GET'])]
    public function templateCategories(): JsonResponse
    {
        $results = $this->em->createQuery(
            'SELECT DISTINCT t.category FROM App\Entity\SocialfuxTemplate t WHERE t.isDeleted = false ORDER BY t.category ASC'
        )->getScalarResult();

        return $this->json(['categories' => array_column($results, 'category')]);
    }

    #[Route('/templates', name: 'socialfux_templates_list', methods: ['GET'])]
    public function listTemplates(): JsonResponse
    {
        $templates = $this->templateRepository->findBy(['isDeleted' => false], ['updatedAt' => 'DESC']);

        return $this->json(['templates' => array_map(fn (SocialfuxTemplate $t) => $t->toArray(), $templates)]);
    }

    #[Route('/templates', name: 'socialfux_template_create', methods: ['POST'])]
    public function createTemplate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $template = new SocialfuxTemplate((string) Uuid::v4());
        $template->setName((string) ($data['name'] ?? 'Unbenannt'));
        $template->setCategory((string) ($data['category'] ?? 'Allgemein'));
        $template->setCanvasPreset((string) ($data['canvasPreset'] ?? ''));
        $template->setCanvasWidth((int) ($data['canvasWidth'] ?? 1080));
        $template->setCanvasHeight((int) ($data['canvasHeight'] ?? 1080));
        $template->setCanvasBackground((string) ($data['canvasBackground'] ?? '#ffffff'));
        $template->setElements(is_array($data['elements'] ?? null) ? $data['elements'] : []);

        $this->em->persist($template);
        $this->em->flush();

        return $this->json($template->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/templates/{id}', name: 'socialfux_template_update', methods: ['PUT', 'PATCH'])]
    public function updateTemplate(string $id, Request $request): JsonResponse
    {
        $template = $this->templateRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$template) {
            throw new NotFoundHttpException('Template not found');
        }

        $data = json_decode($request->getContent(), true) ?? [];
        if (isset($data['name'])) $template->setName((string) $data['name']);
        if (isset($data['category'])) $template->setCategory((string) $data['category']);

        $this->em->flush();

        return $this->json($template->toArray());
    }

    #[Route('/templates/{id}', name: 'socialfux_template_delete', methods: ['DELETE'])]
    public function deleteTemplate(string $id): JsonResponse
    {
        $template = $this->templateRepository->findOneBy(['id' => $id, 'isDeleted' => false]);
        if (!$template) {
            throw new NotFoundHttpException('Template not found');
        }

        $template->setIsDeleted(true);
        $this->em->flush();

        return $this->json(['message' => 'Template deleted']);
    }

    // ── Icons Proxy ────────────────────────────────────────────────────────

    #[Route('/icons/search', name: 'socialfux_icons_search', methods: ['GET'])]
    public function iconsSearch(Request $request): JsonResponse
    {
        $query = (string) $request->query->get('query', '');
        $limit = min((int) $request->query->get('limit', 32), 100);

        $url = 'https://api.iconify.design/search?' . http_build_query(['query' => $query, 'limit' => $limit]);
        $response = @file_get_contents($url);
        if ($response === false) {
            return $this->json(['icons' => [], 'total' => 0]);
        }

        return JsonResponse::fromJsonString($response);
    }

    #[Route('/icons/{prefix}/{name}', name: 'socialfux_icon_get', methods: ['GET'])]
    public function getIcon(string $prefix, string $name, Request $request): JsonResponse
    {
        $color = $request->query->get('color');
        $url = "https://api.iconify.design/{$prefix}/{$name}.svg" . ($color ? '?color=' . rawurlencode($color) : '');
        $svg = @file_get_contents($url);

        if ($svg === false) {
            return $this->json(['error' => 'Icon not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['svg' => $svg, 'prefix' => $prefix, 'name' => $name]);
    }

    // ── Upload ─────────────────────────────────────────────────────────────

    #[Route('/upload', name: 'socialfux_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return $this->json(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $this->json(['error' => 'Invalid file type'], Response::HTTP_BAD_REQUEST);
        }

        $ext = $file->guessExtension() ?? 'png';
        $filename = sprintf('%s-%s.%s', date('Ymd'), bin2hex(random_bytes(6)), $ext);
        $uploadDir = $this->projectDir . '/public/uploads/socialfux';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $file->move($uploadDir, $filename);
        $path = '/uploads/socialfux/' . $filename;

        return $this->json(['url' => $path, 'path' => $path]);
    }

    // ── Images ─────────────────────────────────────────────────────────────

    #[Route('/images/upload', name: 'socialfux_image_upload', methods: ['POST'])]
    public function imageUpload(Request $request): JsonResponse
    {
        return $this->upload($request);
    }

    #[Route('/images', name: 'socialfux_images_list', methods: ['GET'])]
    public function listImages(): JsonResponse
    {
        $uploadDir = $this->projectDir . '/public/uploads/socialfux';
        $images = [];
        if (is_dir($uploadDir)) {
            foreach (new \DirectoryIterator($uploadDir) as $file) {
                if ($file->isFile() && !str_starts_with($file->getBasename(), '.')) {
                    $images[] = [
                        'id' => $file->getBasename('.' . $file->getExtension()),
                        'path' => '/uploads/socialfux/' . $file->getBasename(),
                        'url' => '/uploads/socialfux/' . $file->getBasename(),
                    ];
                }
            }
        }

        return $this->json(['images' => $images]);
    }

    // ── Brands (mock) ──────────────────────────────────────────────────────

    #[Route('/brands', name: 'socialfux_brands', methods: ['GET'])]
    public function brands(Request $request): JsonResponse
    {
        $search = strtolower((string) $request->query->get('search', ''));
        $brands = [
            ['id' => 'google', 'name' => 'Google', 'domain' => 'google.com', 'logo_url' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png'],
            ['id' => 'github', 'name' => 'GitHub', 'domain' => 'github.com', 'logo_url' => 'https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png'],
            ['id' => 'instagram', 'name' => 'Instagram', 'domain' => 'instagram.com', 'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/132px-Instagram_logo_2016.svg.png'],
            ['id' => 'facebook', 'name' => 'Facebook', 'domain' => 'facebook.com', 'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Facebook_Logo_%282019%29.png/1200px-Facebook_Logo_%282019%29.png'],
            ['id' => 'twitter', 'name' => 'Twitter/X', 'domain' => 'twitter.com', 'logo_url' => 'https://abs.twimg.com/responsive-web/client-web/icon-ios.77d25eba.png'],
            ['id' => 'linkedin', 'name' => 'LinkedIn', 'domain' => 'linkedin.com', 'logo_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/LinkedIn_logo_initials.png/800px-LinkedIn_logo_initials.png'],
        ];

        if ($search !== '') {
            $brands = array_values(array_filter($brands, fn ($b) => str_contains(strtolower($b['name']), $search) || str_contains(strtolower($b['domain']), $search)));
        }

        return $this->json(['brands' => $brands]);
    }
}
