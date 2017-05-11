<?php

namespace App\Http\Controllers;
use App\AvailableLayer;
use Illuminate\Http\Request;

class OverlayController extends Controller {
    public $availableGeometryTypes = [
        'Point', 'Linestring', 'Polygon', 'Multipoint', 'Multilinestring', 'Multipolygon'
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    public function getAll() {
        $layers = \DB::table('available_layers as al')
            ->select('al.*', 'ct.thesaurus_url')
            ->orderBy('position', 'asc')
            ->leftJoin('context_types as ct', 'context_type_id', '=', 'ct.id')
            ->get();
        foreach($layers as $l) {
            $label = ContextController::getLabel($l->thesaurus_url);
            $l->label = $label;
            unset($l->thesaurus_url);
        }
        return response()->json([
            'layers' => $layers
        ]);
    }
}
