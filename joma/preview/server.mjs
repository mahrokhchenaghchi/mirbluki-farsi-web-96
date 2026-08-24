/**
 * Arena / local preview for the real JOMA PHP app.
 * Executes the actual PHP files via WebAssembly PHP 7.4.
 * File storage only. No Production deploy. No real MySQL.
 */
import http from "node:http";
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { PHP, PHPRequestHandler } from "@php-wasm/universal";
import { loadNodeRuntime, useHostFilesystem } from "@php-wasm/node";

const here = path.dirname(fileURLToPath(import.meta.url));
const docRoot = path.resolve(here, "..");
const port = Number(process.env.PORT || 8080);
const host = process.env.HOST || "0.0.0.0";
const phpVersion = process.env.JOMA_PHP_VERSION || "7.4";

const mime = {
  ".css": "text/css; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".mjs": "application/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".png": "image/png",
  ".gif": "image/gif",
  ".webp": "image/webp",
  ".svg": "image/svg+xml",
  ".ico": "image/x-icon",
  ".woff": "font/woff",
  ".woff2": "font/woff2",
  ".txt": "text/plain; charset=utf-8",
  ".md": "text/plain; charset=utf-8",
};

function safeJoin(root, urlPath) {
  const decoded = decodeURIComponent(urlPath.split("?")[0]);
  const clean = path.normalize(decoded).replace(/^(\.\.(\/|\\|$))+/, "");
  const full = path.resolve(root, "." + clean);
  if (!full.startsWith(root)) return null;
  return full;
}

function sendStatic(res, file) {
  const ext = path.extname(file).toLowerCase();
  const type = mime[ext] || "application/octet-stream";
  const body = fs.readFileSync(file);
  res.writeHead(200, {
    "Content-Type": type,
    "Content-Length": body.length,
    "Cache-Control": "no-cache",
  });
  res.end(body);
}

function flattenHeaders(headers) {
  const out = {};
  const cookies = [];
  for (const [key, value] of Object.entries(headers || {})) {
    const lower = key.toLowerCase();
    if (lower === "x-frame-options" || lower === "content-security-policy") continue;
    if (lower === "set-cookie") {
      if (Array.isArray(value)) cookies.push(...value);
      else if (value) cookies.push(String(value));
      continue;
    }
    out[key] = Array.isArray(value) ? value.join(", ") : String(value);
  }
  if (cookies.length) out["Set-Cookie"] = cookies;
  return out;
}

async function readBody(req) {
  const chunks = [];
  for await (const chunk of req) chunks.push(chunk);
  return Buffer.concat(chunks);
}

function requestHeaders(req) {
  const headers = {};
  for (const [key, value] of Object.entries(req.headers)) {
    if (value == null) continue;
    headers[key] = Array.isArray(value) ? value.join(", ") : String(value);
  }
  return headers;
}

function publicUrl(req) {
  const forwardedHost = req.headers["x-forwarded-host"];
  const host = String(forwardedHost || req.headers.host || `localhost:${port}`).split(",")[0].trim();
  const proto = String(req.headers["x-forwarded-proto"] || (host.includes("e2b.app") ? "https" : "http"))
    .split(",")[0]
    .trim();
  return { proto, host, origin: `${proto}://${host}` };
}

console.log(`Loading real PHP ${phpVersion} for ${docRoot} ...`);
const php = new PHP(
  await loadNodeRuntime(phpVersion, {
    emscriptenOptions: { processId: 1 },
  }),
);
useHostFilesystem(php);
php.chdir(docRoot);

const handler = new PHPRequestHandler({
  php,
  documentRoot: docRoot,
  absoluteUrl: `http://${host}:${port}`,
  cookieStore: false,
  rewriteRules: [{ match: /^\/?$/, replacement: "/index.php" }],
});

const server = http.createServer(async (req, res) => {
  try {
    const { origin } = publicUrl(req);
    const incoming = new URL(req.url || "/", origin);
    const filePath = safeJoin(docRoot, incoming.pathname);

    if (filePath && fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
      if (!filePath.endsWith(".php")) {
        sendStatic(res, filePath);
        return;
      }
    }

    if (incoming.pathname.startsWith("/data/")) {
      res.writeHead(403, { "Content-Type": "text/plain; charset=utf-8" });
      res.end("Forbidden");
      return;
    }

    const body = await readBody(req);
    const headers = requestHeaders(req);
    headers.host = publicUrl(req).host;
    if (!headers["x-forwarded-proto"]) headers["x-forwarded-proto"] = publicUrl(req).proto;

    const phpUrl = incoming.pathname === "/" ? "/index.php" + incoming.search : incoming.pathname + incoming.search;
    const result = await handler.request({
      method: (req.method || "GET").toUpperCase(),
      url: phpUrl,
      headers,
      body: body.length ? new Uint8Array(body) : undefined,
    });

    if (result.errors) {
      console.error(result.errors);
    }

    const outHeaders = flattenHeaders(result.headers);
    if (!outHeaders["Content-Type"] && !outHeaders["content-type"]) {
      outHeaders["Content-Type"] = "text/html; charset=utf-8";
    }
    res.writeHead(result.httpStatusCode || 200, outHeaders);
    res.end(Buffer.from(result.bytes || []));
  } catch (err) {
    console.error(err);
    if (!res.headersSent) {
      res.writeHead(500, { "Content-Type": "text/html; charset=utf-8" });
    }
    res.end("<!DOCTYPE html><html lang='fa' dir='rtl'><body><h1>خطای پیش‌نمایش</h1><pre>" +
      String(err && err.stack ? err.stack : err).replace(/[<>&]/g, "") +
      "</pre></body></html>");
  }
});

server.listen(port, host, () => {
  console.log(`JOMA PHP preview ready on http://${host}:${port}`);
  console.log(`Document root: ${docRoot}`);
  console.log("This is the real PHP app with file storage. No Production MySQL.");
});
