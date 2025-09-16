<?php

namespace St693ava\FilamentEmailManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use St693ava\FilamentEmailManager\Models\EmailTemplateLayout;

class EmailPreviewController extends Controller
{
    public function previewLayout(EmailTemplateLayout $layout): Response
    {
        $sampleContent = '
            <h1>Sample Email Title</h1>
            <p>This is a preview of how your email layout will look with actual content.</p>

            <p>The layout includes all the professional styling and responsive design from Laravel\'s default email templates.</p>

            <h2>Features included:</h2>
            <ul>
                <li>Responsive design that works on all devices</li>
                <li>Professional typography and spacing</li>
                <li>Consistent styling across email clients</li>
                <li>Header and footer sections</li>
            </ul>

            <p>You can customize this layout or create new ones to match your brand.</p>

            <p>Best regards,<br>Your Application Team</p>
        ';

        $renderedHtml = $layout->renderLayout($sampleContent);

        // Process Blade-like variables for preview
        $renderedHtml = $this->processBladeLikeVariables($renderedHtml);

        return response($renderedHtml, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    protected function processBladeLikeVariables(string $html): string
    {
        // Replace common Laravel config and helper function calls
        $replacements = [
            "{{ config('app.name') }}" => config('app.name'),
            "{{ config('app.url') }}" => config('app.url'),
            "{{ date('Y') }}" => date('Y'),
            '{{{ config(\'app.name\') }}}' => config('app.name'),
            '{{{ config(\'app.url\') }}}' => config('app.url'),
            '{{{ date(\'Y\') }}}' => date('Y'),
        ];

        // Apply replacements
        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        // Handle more complex patterns with regex for config() calls
        $html = preg_replace_callback(
            '/\{\{\s*config\([\'"]([^\'"]+)[\'"]\)\s*\}\}/',
            function ($matches) {
                return config($matches[1], '');
            },
            $html
        );

        // Handle date() function calls
        $html = preg_replace_callback(
            '/\{\{\s*date\([\'"]([^\'"]+)[\'"]\)\s*\}\}/',
            function ($matches) {
                return date($matches[1]);
            },
            $html
        );

        return $html;
    }
}