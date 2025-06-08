<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

class ConverterController extends Controller
{
    private $converter;

    public function __construct()
    {
        // Include the UnitConverter class
        require_once app_path('Classes/UnitConverter.php');
        $this->converter = new \UnitConverter();
    }

    public function index()
    {
        // Get all units and organize them by type
        $unitTypes = $this->organizeUnitsByType();

        return view('converter', compact('unitTypes'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'fromValue' => 'required|numeric',
            'fromUnit' => 'required|string',
            'toUnit' => 'required|string',
        ]);

        try {
            $result = $this->converter->convert(
                $request->fromValue,
                $request->fromUnit,
                $request->toUnit
            );
            function format_significant($number, $significantFigures = null, $decimal = ',')
            {
                if ($number == 0) return '0';

                // Ubah ke string normal
                $str = (string) $number;

                // Deteksi notasi ilmiah dan ubah ke format desimal jika perlu
                if (stripos($str, 'e') !== false) {
                    $str = rtrim(rtrim(number_format($number, 16, '.', ''), '0'), '.');
                }

                // Ganti titik desimal jika diminta
                if ($decimal !== '.') {
                    $str = str_replace('.', $decimal, $str);
                }

                return $str;
            }

            $formattedResult = format_significant($result, 10); // hasil: misalnya 123,46 atau 0,00012345

            session([
                'fromValue'   => $request->fromValue,
                'fromUnit'    => $request->fromUnit,
                'toUnit'      => $request->toUnit,
                'outputValue' => $formattedResult
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'result'     => $formattedResult,
                    'fromValue'  => $request->fromValue,
                    'fromUnit'   => $request->fromUnit,
                    'toUnit'     => $request->toUnit
                ]);
            }



            return redirect()->back()->with('success', 'Conversion successful!');
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function getUnitsByType(Request $request)
    {
        $type = $request->get('type');
        $unitTypes = $this->organizeUnitsByType();

        if (isset($unitTypes[$type])) {
            return response()->json([
                'success' => true,
                'units' => $unitTypes[$type]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unit type not found'
        ]);
    }

    private function organizeUnitsByType()
    {
        // Get all units from the converter
        $allUnits = json_decode($this->converter->getUnit(), true);

        $unitTypes = [
            'length' => [],
            'area' => [],
            'volume' => [],
            'mass' => [],
            'speed' => [],
            'time' => [],
            'angle' => [],
            'pressure' => [],
            'energy' => [],
            'data' => [],
            'frequency' => []
        ];

        // Map units to their categories based on base unit
        foreach ($allUnits as $code => $unit) {
            switch ($unit['base']) {
                case 'm':
                    $unitTypes['length'][$code] = $unit['name'];
                    break;
                case 'm2':
                    $unitTypes['area'][$code] = $unit['name'];
                    break;
                case 'm3':
                    $unitTypes['volume'][$code] = $unit['name'];
                    break;
                case 'kg':
                    $unitTypes['mass'][$code] = $unit['name'];
                    break;
                case 'mps':
                    $unitTypes['speed'][$code] = $unit['name'];
                    break;
                case 's':
                    $unitTypes['time'][$code] = $unit['name'];
                    break;
                case 'deg':
                    $unitTypes['angle'][$code] = $unit['name'];
                    break;
                case 'Pa':
                case 'pa': // Handle case sensitivity issue in original code
                    $unitTypes['pressure'][$code] = $unit['name'];
                    break;
                case 'j':
                    $unitTypes['energy'][$code] = $unit['name'];
                    break;
                case 'bps':
                    $unitTypes['data'][$code] = $unit['name'];
                    break;
                case 'hz':
                    $unitTypes['frequency'][$code] = $unit['name'];
                    break;
            }
        }

        return $unitTypes;
    }
}
