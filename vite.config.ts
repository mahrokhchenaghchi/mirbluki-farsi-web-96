import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";
import { componentTagger } from "lovable-tagger";

// Forces a real file download (Content-Disposition: attachment) for the
// standalone business card, instead of letting the browser just render it.
const cardDownloadPlugin = () => ({
  name: "card-download",
  configureServer(server: { middlewares: { use: (fn: (req: any, res: any, next: () => void) => void) => void } }) {
    server.middlewares.use((req, res, next) => {
      if (req.url === "/download-card") {
        req.url = "/javad-mirbolouki-card.html";
        res.setHeader("Content-Disposition", 'attachment; filename="javad-mirbolouki-card.html"');
      }
      next();
    });
  },
});

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8080,
  },
  plugins: [
    react(),
    cardDownloadPlugin(),
    mode === 'development' &&
    componentTagger(),
  ].filter(Boolean),
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
}));
