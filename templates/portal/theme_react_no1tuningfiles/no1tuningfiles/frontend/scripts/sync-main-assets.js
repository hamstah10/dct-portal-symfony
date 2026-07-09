const fs = require("fs");
const path = require("path");

const cwd = process.cwd();
const buildPath = process.env.BUILD_PATH || "build";
const targetDir = path.resolve(cwd, buildPath);

const newestMatchingFile = (dir, pattern) => {
  if (!fs.existsSync(dir)) {
    return null;
  }

  const candidates = fs
    .readdirSync(dir)
    .filter((name) => pattern.test(name))
    .map((name) => {
      const fullPath = path.join(dir, name);
      const stats = fs.statSync(fullPath);
      return { name, fullPath, mtimeMs: stats.mtimeMs };
    })
    .sort((a, b) => b.mtimeMs - a.mtimeMs);

  return candidates[0] ?? null;
};

const copyIfFound = (from, to) => {
  if (!from || !fs.existsSync(from)) {
    return false;
  }

  fs.copyFileSync(from, to);
  return true;
};

const jsDir = path.join(targetDir, "static", "js");
const cssDir = path.join(targetDir, "static", "css");

const newestJs = newestMatchingFile(jsDir, /^main-[^.].*\.js$/);
const newestCss = newestMatchingFile(cssDir, /^main-[^.].*\.css$/);
const newestJsMap = newestMatchingFile(jsDir, /^main\.js-.*\.map$/);
const newestJsLicense = newestMatchingFile(jsDir, /^main\.js\.LICENSE-.*\.txt$/);
const newestCssMap = newestMatchingFile(cssDir, /^main\.css-.*\.map$/);

const copied = [];

if (copyIfFound(newestJs?.fullPath, path.join(jsDir, "main.js"))) {
  copied.push(`js:${newestJs.name} -> main.js`);
}
if (copyIfFound(newestCss?.fullPath, path.join(cssDir, "main.css"))) {
  copied.push(`css:${newestCss.name} -> main.css`);
}
if (copyIfFound(newestJsMap?.fullPath, path.join(jsDir, "main.js.map"))) {
  copied.push(`jsmap:${newestJsMap.name} -> main.js.map`);
}
if (copyIfFound(newestJsLicense?.fullPath, path.join(jsDir, "main.js.LICENSE.txt"))) {
  copied.push(`license:${newestJsLicense.name} -> main.js.LICENSE.txt`);
}
if (copyIfFound(newestCssMap?.fullPath, path.join(cssDir, "main.css.map"))) {
  copied.push(`cssmap:${newestCssMap.name} -> main.css.map`);
}

if (copied.length > 0) {
  console.log(`[sync-main-assets] ${copied.join(", ")}`);
} else {
  console.log("[sync-main-assets] no hashed main assets found, nothing changed");
}
