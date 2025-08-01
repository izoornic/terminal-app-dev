<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Tests\TestCase;

final class LeafletTest extends TestCase
{
    use InteractsWithViews;

    public function test_it_can_render_the_basic_leaflet_component()
    {
        $content = $this->getComponentRenderedContent('<x-maps-leaflet id="mapId"></x-maps-leaflet>');
        $this->assertStringContainsString('<div id="mapId"></div>', $content);
    }

    public function test_it_can_render_with_a_centre_point()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet :centerPoint=\"['lat' => 52, 'long' => 5]\"></x-maps-leaflet>");
        $this->assertStringContainsString('setView([52, 5]', $content);
    }

    public function test_we_can_set_the_zoom_level()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet :zoomLevel=\"6\"></x-maps-leaflet>");
        $this->assertStringContainsString('setView([0, 0], 6);', $content);
    }

    public function test_it_has_default_styles()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet></x-maps-leaflet>");
        $this->assertStringContainsString('height: 100vh', $content);
    }

    public function test_it_has_can_take_styles_as_attribute()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet style='height: 50vh'></x-maps-leaflet>");
        $this->assertStringContainsString('height: 50vh', $content);
    }

    public function test_it_can_take_classes_as_attribute()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet class='h-16'></x-maps-leaflet>");
        $this->assertStringContainsString("class='h-16'", $content);
    }

    public function test_it_can_take_custom_infowindow_on_marker()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet :markers=\"[['lat' => 38.716450, 'long' => 0.055684, 'info' => 'MarkerInfo']]\"></x-maps-leaflet>");
        $this->assertStringContainsString('marker.bindPopup("MarkerInfo");', $content);
    }

    public function test_it_shows_the_attribution()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet attribution='test'></x-maps-leaflet>");
        $this->assertStringContainsString('test', $content);
    }

    public function test_it_shows_the_default_attribution()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet></x-maps-leaflet>");
        $this->assertStringContainsString('Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors, Imagery © Mapbox.com', $content);
    }

    public function test_uses_latest_as_default_version()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet></x-maps-leaflet>");
        $this->assertStringContainsString('https://unpkg.com/leaflet@latest/dist/leaflet.js', $content);
    }

    public function test_can_pass_in_version_and_it_uses_that()
    {
        $content = $this->getComponentRenderedContent("<x-maps-leaflet leafletVersion='1.9.4'></x-maps-leaflet>");
        $this->assertStringContainsString('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', $content);
    }
}
