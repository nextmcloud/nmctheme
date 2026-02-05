import fs from "fs";

const SRC = "node_modules/@telekom-ods/design-tokens/dist/design-tokens.css";
const DEST = "css/telekom-design-tokens.ods.css";

let css = fs.readFileSync(SRC, "utf8");

// Ersetzungen durchführen
css = css.replace(/:root,\[data-scheme="neutral"\]/g, '[data-scheme="neutral"]');
css = css.replace(/\[data-scheme="macaw"\]/g, ':root,[data-scheme="macaw"]');
css = css.replace(/\[data-mode=/g, '[data-themes*=');

fs.mkdirSync("css", { recursive: true });
fs.writeFileSync(DEST, css);

console.log("✔ ODS tokens fixed");
