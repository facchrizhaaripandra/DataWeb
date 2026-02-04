import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/auth.css",
                "resources/css/dashboard.css",
                "resources/css/dataset.css",
                "resources/js/dataset.js",
                "resources/css/import.css",
                "resources/css/components.css",
            ],
            refresh: true,
        }),
    ],
});
