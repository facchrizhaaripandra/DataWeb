import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/auth.css",
                "resources/js/auth.js",
                "resources/css/dashboard.css",
                "resources/js/dashboard.js",
                "resources/css/dataset.css",
                "resources/js/dataset.js",
                "resources/css/import.css",
                "resources/js/import.js",
                "resources/css/ocr.css",
                "resources/js/ocr.js",
                "resources/css/admin.css",
                "resources/js/admin.js",
                "resources/css/components.css",
            ],
            refresh: true,
        }),
    ],
});
