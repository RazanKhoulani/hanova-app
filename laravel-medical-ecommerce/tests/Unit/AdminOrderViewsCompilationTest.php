<?php

namespace Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminOrderViewsCompilationTest extends TestCase
{
    #[DataProvider('orderViews')]
    public function test_order_views_do_not_leave_uncompiled_control_directives(string $view): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $compiler = new BladeCompiler(new Filesystem(), $projectRoot.'/storage/framework/views');
        $source = file_get_contents($projectRoot.'/resources/views/'.$view);

        $this->assertIsString($source);

        $compiled = $compiler->compileString($source);

        // Parse the generated PHP without rendering it. This catches mismatched
        // compact directives such as `@endif@if`, which caused the production
        // order-details page to return HTTP 500.
        $render = eval('return static function () { ?>'.$compiled.'<?php };');

        $this->assertInstanceOf(\Closure::class, $render);

        $this->assertDoesNotMatchRegularExpression(
            '/@(if|else|elseif|endif|unless|endunless|foreach|endforeach)\b/',
            $compiled,
            "The {$view} template contains adjacent or otherwise uncompiled Blade directives.",
        );
    }

    public static function orderViews(): array
    {
        return [
            'order index' => ['admin/orders/index.blade.php'],
            'order details' => ['admin/orders/show.blade.php'],
        ];
    }
}
