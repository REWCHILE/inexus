<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlueExpressService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $originDistrict;
    protected int $originRegion;

    public function __construct()
    {
        try {
            $this->baseUrl = trim((string) Setting::get('bluex_base_url', 'https://eplin.api.blue.cl'));
            $this->apiKey = trim((string) Setting::get('bluex_api_key', 'QUoO07ZRZ12tzkkF8yJM9am7uhxUJCbR7f6kU5Dz'));
            $this->originDistrict = trim((string) Setting::get('bluex_origin_district', 'SCL'));
            $this->originRegion = (int) Setting::get('bluex_origin_region', 13);
        } catch (\Throwable $e) {
            $this->baseUrl = 'https://eplin.api.blue.cl';
            $this->apiKey = 'QUoO07ZRZ12tzkkF8yJM9am7uhxUJCbR7f6kU5Dz';
            $this->originDistrict = 'SCL';
            $this->originRegion = 13;
        }
    }

    /**
     * Get list of Chilean Regions with their codes
     */
    public function getRegions(): array
    {
        return [
            'CL-RM' => ['name' => 'Región Metropolitana de Santiago', 'num' => 13, 'short' => 'RM'],
            'CL-VS' => ['name' => 'Región de Valparaíso', 'num' => 5, 'short' => 'VS'],
            'CL-BI' => ['name' => 'Región del Biobío', 'num' => 8, 'short' => 'BI'],
            'CL-AN' => ['name' => 'Región de Antofagasta', 'num' => 2, 'short' => 'AN'],
            'CL-CO' => ['name' => 'Región de Coquimbo', 'num' => 4, 'short' => 'CO'],
            'CL-LI' => ['name' => 'Región de O\'Higgins', 'num' => 6, 'short' => 'LI'],
            'CL-ML' => ['name' => 'Región del Maule', 'num' => 7, 'short' => 'ML'],
            'CL-NB' => ['name' => 'Región de Ñuble', 'num' => 16, 'short' => 'NB'],
            'CL-AR' => ['name' => 'Región de La Araucanía', 'num' => 9, 'short' => 'AR'],
            'CL-LR' => ['name' => 'Región de Los Ríos', 'num' => 14, 'short' => 'LR'],
            'CL-LL' => ['name' => 'Región de Los Lagos', 'num' => 10, 'short' => 'LL'],
            'CL-AT' => ['name' => 'Región de Atacama', 'num' => 3, 'short' => 'AT'],
            'CL-TA' => ['name' => 'Región de Tarapacá', 'num' => 1, 'short' => 'TA'],
            'CL-AP' => ['name' => 'Región de Arica y Parinacota', 'num' => 15, 'short' => 'AP'],
            'CL-AI' => ['name' => 'Región de Aysén', 'num' => 11, 'short' => 'AI'],
            'CL-MA' => ['name' => 'Región de Magallanes', 'num' => 12, 'short' => 'MA'],
        ];
    }

    /**
     * Full mapping of Chilean Communes by Region Code
     */
    public function getCommunesByRegion(?string $regionCode = null): array
    {
        $communes = [
            'CL-RM' => [
                ['name' => 'Santiago', 'code' => 'SCL'],
                ['name' => 'Providencia', 'code' => 'PRO'],
                ['name' => 'Las Condes', 'code' => 'LCD'],
                ['name' => 'Vitacura', 'code' => 'VTC'],
                ['name' => 'Lo Barnechea', 'code' => 'LBR'],
                ['name' => 'Ñuñoa', 'code' => 'NNA'],
                ['name' => 'La Reina', 'code' => 'LRN'],
                ['name' => 'Macul', 'code' => 'MAC'],
                ['name' => 'Peñalolén', 'code' => 'PNL'],
                ['name' => 'La Florida', 'code' => 'LFD'],
                ['name' => 'San Miguel', 'code' => 'SMG'],
                ['name' => 'San Joaquín', 'code' => 'SJQ'],
                ['name' => 'La Cisterna', 'code' => 'LCN'],
                ['name' => 'La Granja', 'code' => 'LGJ'],
                ['name' => 'La Pintana', 'code' => 'LPT'],
                ['name' => 'San Ramón', 'code' => 'SRN'],
                ['name' => 'El Bosque', 'code' => 'EBO'],
                ['name' => 'Pedro Aguirre Cerda', 'code' => 'PAC'],
                ['name' => 'Lo Espejo', 'code' => 'LEP'],
                ['name' => 'Estación Central', 'code' => 'ECE'],
                ['name' => 'Cerrillos', 'code' => 'RRI'],
                ['name' => 'Maipú', 'code' => 'MAI'],
                ['name' => 'Quinta Normal', 'code' => 'QTN'],
                ['name' => 'Lo Prado', 'code' => 'LPR'],
                ['name' => 'Pudahuel', 'code' => 'PUD'],
                ['name' => 'Cerro Navia', 'code' => 'CNV'],
                ['name' => 'Renca', 'code' => 'REN'],
                ['name' => 'Quilicura', 'code' => 'QLC'],
                ['name' => 'Conchalí', 'code' => 'CNH'],
                ['name' => 'Huechuraba', 'code' => 'HRB'],
                ['name' => 'Recoleta', 'code' => 'RLT'],
                ['name' => 'Independencia', 'code' => 'IDP'],
                ['name' => 'San Bernardo', 'code' => 'SBD'],
                ['name' => 'Puente Alto', 'code' => 'PAL'],
                ['name' => 'Pirque', 'code' => 'PIR'],
                ['name' => 'San José de Maipo', 'code' => 'SJS'],
                ['name' => 'Colina', 'code' => 'COL'],
                ['name' => 'Lampa', 'code' => 'LSG'],
                ['name' => 'Tiltil', 'code' => 'TIL'],
                ['name' => 'Alhué', 'code' => 'ALH'],
                ['name' => 'Buin', 'code' => 'ZBU'],
                ['name' => 'Calera de Tango', 'code' => 'CDT'],
                ['name' => 'Curacaví', 'code' => 'CVI'],
                ['name' => 'El Monte', 'code' => 'ZTE'],
                ['name' => 'Isla de Maipo', 'code' => 'IDM'],
                ['name' => 'María Pinto', 'code' => 'MPO'],
                ['name' => 'Melipilla', 'code' => 'ZMP'],
                ['name' => 'Padre Hurtado', 'code' => 'PHT'],
                ['name' => 'Paine', 'code' => 'ZPN'],
                ['name' => 'Peñaflor', 'code' => 'PFL'],
                ['name' => 'San Pedro', 'code' => 'SPO'],
                ['name' => 'Talagante', 'code' => 'TNT'],
            ],
            'CL-VS' => [
                ['name' => 'Valparaíso', 'code' => 'VAP'],
                ['name' => 'Viña del Mar', 'code' => 'KNA'],
                ['name' => 'Concón', 'code' => 'CON'],
                ['name' => 'Quilpué', 'code' => 'QPE'],
                ['name' => 'Villa Alemana', 'code' => 'VIA'],
                ['name' => 'Quillota', 'code' => 'QTA'],
                ['name' => 'La Calera', 'code' => 'ZLC'],
                ['name' => 'Limache', 'code' => 'LIC'],
                ['name' => 'Olmué', 'code' => 'OLM'],
                ['name' => 'San Antonio', 'code' => 'SNT'],
                ['name' => 'Cartagena', 'code' => 'CRT'],
                ['name' => 'El Quisco', 'code' => 'EQO'],
                ['name' => 'El Tabo', 'code' => 'ETB'],
                ['name' => 'Algarrobo', 'code' => 'ABO'],
                ['name' => 'Santo Domingo', 'code' => 'SDC'],
                ['name' => 'Los Andes', 'code' => 'LOB'],
                ['name' => 'San Felipe', 'code' => 'SFP'],
                ['name' => 'La Ligua', 'code' => 'LLC'],
                ['name' => 'Papudo', 'code' => 'PPO'],
                ['name' => 'Zapallar', 'code' => 'ZAR'],
                ['name' => 'Puchuncaví', 'code' => 'PCV'],
                ['name' => 'Quintero', 'code' => 'QTO'],
                ['name' => 'Casablanca', 'code' => 'CBC'],
                ['name' => 'Cabildo', 'code' => 'CDO'],
                ['name' => 'Calle Larga', 'code' => 'CLG'],
                ['name' => 'Catemu', 'code' => 'CAT'],
                ['name' => 'Hijuelas', 'code' => 'HJS'],
                ['name' => 'La Cruz', 'code' => 'LCZ'],
                ['name' => 'Llaillay', 'code' => 'LLY'],
                ['name' => 'Nogales', 'code' => 'NOG'],
                ['name' => 'Panquehue', 'code' => 'PNQ'],
                ['name' => 'Petorca', 'code' => 'PTK'],
                ['name' => 'Putaendo', 'code' => 'PUT'],
                ['name' => 'Rinconada', 'code' => 'RDA'],
                ['name' => 'San Esteban', 'code' => 'SEN'],
                ['name' => 'Santa María', 'code' => 'SRI'],
                ['name' => 'Isla de Pascua', 'code' => 'IPC'],
                ['name' => 'Juan Fernández', 'code' => 'JFZ'],
            ],
            'CL-BI' => [
                ['name' => 'Concepción', 'code' => 'CCP'],
                ['name' => 'San Pedro de la Paz', 'code' => 'SPP'],
                ['name' => 'Talcahuano', 'code' => 'ZTO'],
                ['name' => 'Chiguayante', 'code' => 'CYE'],
                ['name' => 'Hualpén', 'code' => 'HLP'],
                ['name' => 'Coronel', 'code' => 'CRN'],
                ['name' => 'Lota', 'code' => 'LOT'],
                ['name' => 'Penco', 'code' => 'PCO'],
                ['name' => 'Tomé', 'code' => 'TMC'],
                ['name' => 'Hualqui', 'code' => 'HLQ'],
                ['name' => 'Los Ángeles', 'code' => 'LSQ'],
                ['name' => 'Arauco', 'code' => 'ARA'],
                ['name' => 'Cabrero', 'code' => 'CRO'],
                ['name' => 'Cañete', 'code' => 'CTE'],
                ['name' => 'Curanilahue', 'code' => 'ZHE'],
                ['name' => 'Lebu', 'code' => 'ZLB'],
                ['name' => 'Mulchén', 'code' => 'MUL'],
                ['name' => 'Nacimiento', 'code' => 'NAC'],
                ['name' => 'Yumbel', 'code' => 'ZYU'],
                ['name' => 'Laja', 'code' => 'LLJ'],
                ['name' => 'Santa Bárbara', 'code' => 'SBB'],
                ['name' => 'Santa Juana', 'code' => 'SJN'],
                ['name' => 'Tirúa', 'code' => 'TUA'],
                ['name' => 'Tucapel', 'code' => 'TCP'],
                ['name' => 'Alto Biobío', 'code' => 'AOO'],
                ['name' => 'Antuco', 'code' => 'ANT'],
                ['name' => 'Contulmo', 'code' => 'CTU'],
                ['name' => 'Florida', 'code' => 'FLO'],
                ['name' => 'Los Álamos', 'code' => 'LAL'],
                ['name' => 'Negrete', 'code' => 'NRE'],
                ['name' => 'Quilaco', 'code' => 'QCO'],
                ['name' => 'Quilleco', 'code' => 'QLO'],
                ['name' => 'San Rosendo', 'code' => 'SRO'],
            ],
            'CL-AN' => [
                ['name' => 'Antofagasta', 'code' => 'ANF'],
                ['name' => 'Calama', 'code' => 'CJC'],
                ['name' => 'Mejillones', 'code' => 'MJS'],
                ['name' => 'San Pedro de Atacama', 'code' => 'SPX'],
                ['name' => 'Tocopilla', 'code' => 'TOC'],
                ['name' => 'Taltal', 'code' => 'TTL'],
                ['name' => 'Sierra Gorda', 'code' => 'SGD'],
                ['name' => 'María Elena', 'code' => 'MAE'],
                ['name' => 'Ollagüe', 'code' => 'OLL'],
            ],
            'CL-CO' => [
                ['name' => 'La Serena', 'code' => 'LSC'],
                ['name' => 'Coquimbo', 'code' => 'COQ'],
                ['name' => 'Ovalle', 'code' => 'OVL'],
                ['name' => 'Illapel', 'code' => 'ILL'],
                ['name' => 'Los Vilos', 'code' => 'LVL'],
                ['name' => 'Salamanca', 'code' => 'SCA'],
                ['name' => 'Vicuña', 'code' => 'VCA'],
                ['name' => 'Andacollo', 'code' => 'ACO'],
                ['name' => 'Combarbalá', 'code' => 'COB'],
                ['name' => 'Monte Patria', 'code' => 'MPC'],
                ['name' => 'Canela', 'code' => 'CNE'],
                ['name' => 'La Higuera', 'code' => 'LHC'],
                ['name' => 'Paiguano', 'code' => 'PHO'],
                ['name' => 'Punitaqui', 'code' => 'PTQ'],
                ['name' => 'Río Hurtado', 'code' => 'RHU'],
            ],
            'CL-LI' => [
                ['name' => 'Rancagua', 'code' => 'RCG'],
                ['name' => 'Machalí', 'code' => 'MCH'],
                ['name' => 'San Fernando', 'code' => 'SFR'],
                ['name' => 'Rengo', 'code' => 'ZRG'],
                ['name' => 'Santa Cruz', 'code' => 'ZSC'],
                ['name' => 'Graneros', 'code' => 'GRA'],
                ['name' => 'San Vicente', 'code' => 'SVT'],
                ['name' => 'Pichilemu', 'code' => 'PMU'],
                ['name' => 'Requínoa', 'code' => 'REQ'],
                ['name' => 'Mostazal', 'code' => 'SFM'],
                ['name' => 'Chimbarongo', 'code' => 'CHB'],
                ['name' => 'Doñihue', 'code' => 'DNE'],
                ['name' => 'Coltauco', 'code' => 'CTO'],
                ['name' => 'Las Cabras', 'code' => 'LCB'],
                ['name' => 'Nancagua', 'code' => 'NGA'],
                ['name' => 'Peralillo', 'code' => 'ZPE'],
                ['name' => 'Peumo', 'code' => 'PEO'],
                ['name' => 'Pichidegua', 'code' => 'PHA'],
            ],
            'CL-ML' => [
                ['name' => 'Talca', 'code' => 'ZCA'],
                ['name' => 'Curicó', 'code' => 'CCO'],
                ['name' => 'Linares', 'code' => 'LNR'],
                ['name' => 'Constitución', 'code' => 'CTT'],
                ['name' => 'Cauquenes', 'code' => 'CQE'],
                ['name' => 'Molina', 'code' => 'ZMO'],
                ['name' => 'Parral', 'code' => 'PRR'],
                ['name' => 'San Javier', 'code' => 'SJA'],
                ['name' => 'San Clemente', 'code' => 'STE'],
                ['name' => 'Maule', 'code' => 'ZMA'],
                ['name' => 'Teno', 'code' => 'TEN'],
                ['name' => 'Longaví', 'code' => 'LGV'],
                ['name' => 'Colbún', 'code' => 'CLB'],
                ['name' => 'Villa Alegre', 'code' => 'VGE'],
            ],
            'CL-NB' => [
                ['name' => 'Chillán', 'code' => 'YAI'],
                ['name' => 'Chillán Viejo', 'code' => 'YAV'],
                ['name' => 'San Carlos', 'code' => 'SCS'],
                ['name' => 'Bulnes', 'code' => 'BLN'],
                ['name' => 'Quillón', 'code' => 'QLL'],
                ['name' => 'Coihueco', 'code' => 'CUH'],
                ['name' => 'Yungay', 'code' => 'YGY'],
                ['name' => 'Quirihue', 'code' => 'QIH'],
                ['name' => 'Coelemu', 'code' => 'ZOU'],
            ],
            'CL-AR' => [
                ['name' => 'Temuco', 'code' => 'ZCO'],
                ['name' => 'Padre las Casas', 'code' => 'PCS'],
                ['name' => 'Villarrica', 'code' => 'VRR'],
                ['name' => 'Pucón', 'code' => 'ZPU'],
                ['name' => 'Angol', 'code' => 'ZOL'],
                ['name' => 'Lautaro', 'code' => 'LTR'],
                ['name' => 'Victoria', 'code' => 'VIC'],
                ['name' => 'Nueva Imperial', 'code' => 'NIP'],
                ['name' => 'Pitrufquén', 'code' => 'PQN'],
                ['name' => 'Loncoche', 'code' => 'LOC'],
                ['name' => 'Collipulli', 'code' => 'CPI'],
                ['name' => 'Traiguén', 'code' => 'ZEN'],
            ],
            'CL-LR' => [
                ['name' => 'Valdivia', 'code' => 'ZAL'],
                ['name' => 'La Unión', 'code' => 'ZLU'],
                ['name' => 'Río Bueno', 'code' => 'RBN'],
                ['name' => 'Panguipulli', 'code' => 'PGP'],
                ['name' => 'Paillaco', 'code' => 'PAI'],
                ['name' => 'Los Lagos', 'code' => 'LAG'],
                ['name' => 'Lanco', 'code' => 'LNC'],
                ['name' => 'Mariquina', 'code' => 'MQA'],
                ['name' => 'Futrono', 'code' => 'FTR'],
            ],
            'CL-LL' => [
                ['name' => 'Puerto Montt', 'code' => 'PMC'],
                ['name' => 'Puerto Varas', 'code' => 'ZPV'],
                ['name' => 'Osorno', 'code' => 'ZOS'],
                ['name' => 'Castro', 'code' => 'CTR'],
                ['name' => 'Ancud', 'code' => 'ACD'],
                ['name' => 'Quellón', 'code' => 'QLN'],
                ['name' => 'Llanquihue', 'code' => 'LLQ'],
                ['name' => 'Frutillar', 'code' => 'FRT'],
                ['name' => 'Calbuco', 'code' => 'CBU'],
                ['name' => 'Purranque', 'code' => 'PRE'],
                ['name' => 'Chaitén', 'code' => 'ZCN'],
            ],
            'CL-AT' => [
                ['name' => 'Copiapó', 'code' => 'CPO'],
                ['name' => 'Vallenar', 'code' => 'VAL'],
                ['name' => 'Caldera', 'code' => 'CLR'],
                ['name' => 'Chañaral', 'code' => 'CHN'],
                ['name' => 'Diego de Almagro', 'code' => 'DAG'],
                ['name' => 'Huasco', 'code' => 'HCO'],
                ['name' => 'Tierra Amarilla', 'code' => 'TRM'],
            ],
            'CL-TA' => [
                ['name' => 'Iquique', 'code' => 'IQQ'],
                ['name' => 'Alto Hospicio', 'code' => 'AHP'],
                ['name' => 'Pozo Almonte', 'code' => 'PAM'],
                ['name' => 'Pica', 'code' => 'OPC'],
                ['name' => 'Huara', 'code' => 'HRA'],
            ],
            'CL-AP' => [
                ['name' => 'Arica', 'code' => 'ARI'],
                ['name' => 'Camarones', 'code' => 'CAM'],
                ['name' => 'Putre', 'code' => 'PTR'],
            ],
            'CL-AI' => [
                ['name' => 'Coyhaique', 'code' => 'GXQ'],
                ['name' => 'Aysén', 'code' => 'WPA'],
                ['name' => 'Chile Chico', 'code' => 'CCH'],
                ['name' => 'Cochrane', 'code' => 'CCL'],
                ['name' => 'Cisnes', 'code' => 'CNS'],
            ],
            'CL-MA' => [
                ['name' => 'Punta Arenas', 'code' => 'PUQ'],
                ['name' => 'Natales', 'code' => 'PNT'],
                ['name' => 'Porvenir', 'code' => 'ZPR'],
                ['name' => 'Cabo de Hornos', 'code' => 'HOR'],
            ]
        ];

        if ($regionCode) {
            return $communes[$regionCode] ?? [];
        }

        return $communes;
    }

    /**
     * Resolve Blue Express Geolocation (districtCode + regionCode)
     */
    public function resolveGeolocation(string $regionCode, string $communeName): ?array
    {
        $cacheKey = 'bx_geo_' . md5($regionCode . '_' . mb_strtolower(trim($communeName)));
        return Cache::remember($cacheKey, 86400, function () use ($regionCode, $communeName) {
            // First check local communes list
            $communes = $this->getCommunesByRegion($regionCode);
            $localDistrictCode = null;
            foreach ($communes as $c) {
                if (mb_strtolower($c['name']) === mb_strtolower(trim($communeName))) {
                    $localDistrictCode = $c['code'];
                    break;
                }
            }

            // Region number
            $regions = $this->getRegions();
            $regionNum = $regions[$regionCode]['num'] ?? 13;
            $regionShort = $regions[$regionCode]['short'] ?? 'RM';

            try {
                $response = Http::timeout(8)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-api-key' => $this->apiKey,
                    ])
                    ->post("{$this->baseUrl}/api/ecommerce/comunas/v1/bxgeo", [
                        'address'    => $communeName,
                        'type'       => 'woocommerce',
                        'shop'       => 'https://inexus.cl/',
                        'regionCode' => $regionShort,
                        'agencyId'   => ''
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['districtCode'])) {
                        return [
                            'districtCode' => $data['districtCode'],
                            'regionCode'   => $data['regionCode'] ?? $regionNum,
                            'communeName'  => $data['cidadeName'] ?? $communeName,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("BlueExpress Geolocation error for {$communeName}: " . $e->getMessage());
            }

            // Fallback to local code if available
            if ($localDistrictCode) {
                return [
                    'districtCode' => $localDistrictCode,
                    'regionCode'   => $regionNum,
                    'communeName'  => $communeName,
                ];
            }

            return null;
        });
    }

    /**
     * Quote shipping rate via Blue Express Pricing API
     * Supports both 'domicilio' (PAQU) and 'pickup' (PUDO - Punto Blue Express)
     */
    public function quoteShipping(string $regionCode, string $communeName, array $cart = [], float $subtotal = 0.0, string $shippingType = 'domicilio'): array
    {
        $shippingType = strtolower(trim($shippingType)) === 'pickup' ? 'pickup' : 'domicilio';
        $familiaProducto = $shippingType === 'pickup' ? 'PUDO' : 'PAQU';
        $defaultServiceName = $shippingType === 'pickup' ? 'Blue Express Punto Pick Up (Retiro en Punto Blue)' : 'Blue Express Express (A Domicilio)';

        // Free shipping is only applied if explicitly enabled by admin in settings
        $isFree = false;
        try {
            $freeShippingEnabled = (bool) Setting::get('free_shipping_enabled', false);
            $freeShippingThreshold = (float) Setting::get('free_shipping_threshold', 0);
            if ($freeShippingEnabled && $freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold) {
                $isFree = true;
            }
        } catch (\Throwable $e) {
            $isFree = false;
        }

        // Resolve destination district & region
        $geo = $this->resolveGeolocation($regionCode, $communeName);
        if (!$geo) {
            $fallbackCost = $shippingType === 'pickup' ? 3490 : 4990;
            return [
                'success' => true,
                'is_fallback' => true,
                'courier' => 'Blue Express',
                'service_name' => $defaultServiceName,
                'shipping_type' => $shippingType,
                'cost' => $isFree ? 0 : $fallbackCost,
                'real_cost' => $fallbackCost,
                'promise_day' => 'Hasta 2 a 4 días hábiles',
                'is_free' => $isFree,
            ];
        }

        // Calculate package weight & bultos
        $totalWeight = 0;
        $bultos = [];
        $itemCount = 0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $itemCount += $qty;
            $itemWeight = (float) ($item['weight'] ?? 1.2);
            $totalWeight += $itemWeight * $qty;
        }

        $totalWeight = max(1.0, round($totalWeight, 1));
        $bultos[] = [
            'largo'      => 20,
            'ancho'      => 20,
            'alto'       => max(10, min(80, 10 * count($cart))),
            'sku'        => 'INX-CART',
            'pesoFisico' => $totalWeight,
            'cantidad'   => 1,
        ];

        $cacheKey = 'bx_rate_' . md5($geo['districtCode'] . '_' . $geo['regionCode'] . '_' . $totalWeight . '_' . (int)$subtotal . '_' . $shippingType);
        $cachedQuote = Cache::get($cacheKey);
        if ($cachedQuote) {
            if ($isFree) {
                $cachedQuote['cost'] = 0;
                $cachedQuote['is_free'] = true;
            }
            return $cachedQuote;
        }

        $payload = [
            'from' => [
                'country'  => 'CL',
                'district' => $this->originDistrict,
            ],
            'to' => [
                'country'  => 'CL',
                'state'    => (string) $geo['regionCode'],
                'district' => $geo['districtCode'],
            ],
            'serviceType' => 'EX', // Blue Express Express Terrestre
            'domain'      => 'https://inexus.cl/',
            'datosProducto' => [
                'producto'        => 'P',
                'familiaProducto' => $familiaProducto,
                'bultos'          => $bultos,
            ]
        ];

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'x-api-key'    => $this->apiKey,
                    'price'        => (string) max(1000, (int) $subtotal),
                ])
                ->post("{$this->baseUrl}/eplin/pricing/v1", $payload);

            if ($response->successful()) {
                $resData = $response->json();
                $data = $resData['data'] ?? [];

                if (!empty($data['total'])) {
                    $realCost = (int) round($data['total']);
                    $promiseDay = $data['promiseDay'] ?? 'Hasta 2 a 3 días hábiles';
                    $serviceName = $data['nameService'] ?? $defaultServiceName;
                    if ($shippingType === 'pickup' && !str_contains(strtolower($serviceName), 'pick') && !str_contains(strtolower($serviceName), 'punto')) {
                        $serviceName = 'Blue Express - Punto Pick Up';
                    }

                    $quote = [
                        'success'      => true,
                        'is_fallback'  => false,
                        'courier'      => 'Blue Express',
                        'service_name' => $serviceName,
                        'shipping_type'=> $shippingType,
                        'cost'         => $isFree ? 0 : $realCost,
                        'real_cost'    => $realCost,
                        'promise_day'  => $promiseDay,
                        'is_free'      => $isFree,
                        'district'     => $geo['districtCode'],
                        'commune'      => $geo['communeName'],
                    ];

                    Cache::put($cacheKey, $quote, 900); // 15 mins cache
                    return $quote;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("BlueExpress Pricing API exception: " . $e->getMessage());
        }

        // Sensible fallback based on zone
        $fallbackCost = 4990;
        if ($geo['regionCode'] === 13) {
            $fallbackCost = $shippingType === 'pickup' ? 2490 : 3490; // Santiago
        } elseif (in_array($geo['regionCode'], [5, 6, 7])) {
            $fallbackCost = $shippingType === 'pickup' ? 3490 : 4490; // Central
        } else {
            $fallbackCost = $shippingType === 'pickup' ? 4490 : 5990; // Regions
        }

        return [
            'success'      => true,
            'is_fallback'  => true,
            'courier'      => 'Blue Express',
            'service_name' => $defaultServiceName,
            'shipping_type'=> $shippingType,
            'cost'         => $isFree ? 0 : $fallbackCost,
            'real_cost'    => $fallbackCost,
            'promise_day'  => 'Hasta 2 a 3 días hábiles',
            'is_free'      => $isFree,
            'district'     => $geo['districtCode'],
            'commune'      => $geo['communeName'],
        ];
    }
}
