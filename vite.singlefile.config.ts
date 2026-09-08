import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";

/**
 * کانفیگ مخصوص ساخت «فایل دموی تک‌فایلی» — demo/pizza-royal-demo.html
 *
 * تفاوت با کانفیگ اصلی:
 *  - base "./"            → مسیرهای نسبی (قابل باز شدن با file://)
 *  - inlineDynamicImports → همه چانک‌ها (از جمله پنل ادمین) داخل یک فایل
 *  - outDir "dist-single" → خروجی جدا از بیلد اصلی
 *
 * برای تولید فایل نهایی:  node scripts/build-single-file.mjs
 */
export default defineConfig({
  base: "./",
  plugins: [react()],
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
    },
  },
  build: {
    outDir: "dist-single",
    chunkSizeWarningLimit: 5000,
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
      },
    },
  },
});
