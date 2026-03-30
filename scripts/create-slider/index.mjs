#!/usr/bin/env node
import fs from "node:fs";
import path from "node:path";

const targetArg = process.argv[2] || "slider-dev";
const targetDir = path.resolve(process.cwd(), targetArg);

const files = {
  "package.json": JSON.stringify(
    {
      name: path.basename(targetDir),
      private: true,
      scripts: {
        dev: "vite",
        build: "vite build",
        preview: "vite preview"
      },
      devDependencies: {
        vite: "^5.4.0",
        typescript: "^5.6.0"
      }
    },
    null,
    2
  ) + "\n",
  "src/main.ts": "console.log('Syntekpro Slider custom layer dev environment ready.');\n",
  "src/layers/custom-layer.ts": "export function registerCustomLayer() {\n  return { type: 'custom-layer', render: () => '<div>Custom Layer</div>' };\n}\n",
  "index.html": "<!doctype html><html><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Slider Dev</title></head><body><div id=\"app\"></div><script type=\"module\" src=\"/src/main.ts\"></script></body></html>\n",
  "README.md": "# Slider Dev Scaffold\n\nRun:\n\n```bash\nnpm install\nnpm run dev\n```\n\nThen connect your custom layer output to Syntekpro slider hooks.\n"
};

for (const [name, content] of Object.entries(files)) {
  const filePath = path.join(targetDir, name);
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
  fs.writeFileSync(filePath, content, "utf8");
}

console.log(`Scaffolded ${targetDir}`);
console.log("Next: cd " + targetArg + " && npm install && npm run dev");
