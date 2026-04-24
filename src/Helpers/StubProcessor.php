<?php

namespace Roldante05\ScaffoldingFactory\Helpers;

class StubProcessor
{
    /**
     * Procesa un contenido de plantilla con variables y bloques condicionales.
     */
    public static function process(string $content, array $variables, array $tags): string
    {
        // 1. Reemplazar variables {{VAR}}
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        // 2. Procesar bloques condicionales {{TAG}} ... {{/TAG}}
        foreach ($tags as $tag => $condition) {
            // Bloque positivo: {{TAG}} ... {{/TAG}}
            $content = preg_replace_callback(
                '/\{\{' . $tag . '\}\}(.*?)\{\{\/' . $tag . '\}\}/s',
                function (array $matches) use ($condition): string {
                    return $condition ? trim($matches[1]) . "\n" : '';
                },
                $content
            );

            // Bloque negado: {{!TAG}} ... {{/!TAG}}
            $content = preg_replace_callback(
                '/\{\{!' . $tag . '\}\}(.*?)\{\{\/!' . $tag . '\}\}/s',
                function (array $matches) use ($condition): string {
                    return !$condition ? trim($matches[1]) . "\n" : '';
                },
                $content
            );
        }

        // Limpiar líneas vacías consecutivas (máximo 1)
        return preg_replace("/\n{3,}/", "\n\n", $content);
    }
}
