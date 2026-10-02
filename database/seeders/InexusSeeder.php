<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InexusSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Admin User
        $admin = User::firstOrNew(['email' => 'admin@inexus.cl']);
        $admin->name = 'Administrador INEXUS';
        $admin->password = Hash::make('inexus2026!');
        $admin->is_admin = true;
        $admin->email_verified_at = now();
        $admin->save();

        // 1.1 Create Demo Customer User
        $customer = User::firstOrNew(['email' => 'cliente@inexus.cl']);
        $customer->name = 'Carlos Valenzuela';
        $customer->password = Hash::make('cliente2026!');
        $customer->phone = '+56 9 8765 4321';
        $customer->rut = '16.892.451-8';
        $customer->account_type = 'company';
        $customer->company_name = 'Tecnología y Redes SpA';
        $customer->company_rut = '76.892.451-K';
        $customer->company_giro = 'Servicios Informáticos y Redes';
        $customer->shipping_address = 'Av. Apoquindo 4700, Piso 8';
        $customer->shipping_city = 'Las Condes, Santiago';
        $customer->shipping_region = 'Metropolitana';
        $customer->is_admin = false;
        $customer->email_verified_at = now();
        $customer->save();

        // 2. Default Store Settings
        $defaultSettings = [
            'ingram_client_id' => 'INGRAM_SANDBOX_CLIENT_ID_CL',
            'ingram_client_secret' => 'INGRAM_SANDBOX_SECRET_KEY_CL',
            'ingram_customer_number' => '20-847291',
            'ingram_country_code' => 'CL',
            'ingram_environment' => 'sandbox',
            'ingram_global_margin' => 18.0,
            'ingram_usd_exchange_rate' => 965.0,
            'ingram_preserve_scraped_data' => true,
            'mercadopago_public_key' => 'TEST-098234-pub-cl',
            'mercadopago_access_token' => 'TEST-74928192837491-092812-inexus-token-chile',
            'mercadopago_sandbox' => true,
            'flow_api_key' => '',
            'flow_secret_key' => '',
            'flow_sandbox' => true,
            'flow_active' => true,
            'store_phone' => '+56 2 2987 6543',
            'store_whatsapp' => '+56987654321',
            'store_email' => 'contacto@inexus.cl',
            'store_address' => 'Av. Providencia 1208, Of. 601, Providencia, Santiago, Chile',
        ];

        foreach ($defaultSettings as $key => $val) {
            Setting::set($key, $val, is_bool($val) ? 'boolean' : (is_numeric($val) ? 'float' : 'text'), 'store');
        }

        // 3. Core Categories matching screenshot icons
        $categoriesData = [
            [
                'name' => 'Computadores de Escritorio',
                'slug' => 'computadores-de-escritorio',
                'icon' => 'images/categories/escritorio.svg',
                'description' => 'Workstations, PCs para empresas y computadores de alta potencia para oficina y renderizado.',
                'margin_percentage' => 16.0,
                'sort_order' => 1,
            ],
            [
                'name' => 'Computadores Portátiles/Tablets',
                'slug' => 'computadores-portatiles-tablets',
                'icon' => 'images/categories/NOTEBOOK.png',
                'description' => 'Notebooks empresariales, ultrabooks ligeros y tablets profesionales de las mejores marcas.',
                'margin_percentage' => 15.0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Discos de Estado Sólido (SSD)',
                'slug' => 'discos-de-estado-solido-ssd',
                'icon' => 'images/categories/SSD.png',
                'description' => 'Unidades de almacenamiento NVMe M.2, SATA y PCIe para alto rendimiento corporativo.',
                'margin_percentage' => 22.0,
                'sort_order' => 3,
            ],
            [
                'name' => 'Dispositivos de Entrada',
                'slug' => 'dispositivos-de-entrada',
                'icon' => 'images/categories/PERIFERICOS.png',
                'description' => 'Teclados mecánicos, mouses ergonómicos, combos inalámbricos y periféricos de precisión.',
                'margin_percentage' => 25.0,
                'sort_order' => 4,
            ],
            [
                'name' => 'Impresoras',
                'slug' => 'impresoras',
                'icon' => 'images/categories/IMPRESORAS.png',
                'description' => 'Impresoras multifuncionales láser, de tinta continua y equipos departamentales de alta demanda.',
                'margin_percentage' => 18.0,
                'sort_order' => 5,
            ],
            [
                'name' => 'Pantallas',
                'slug' => 'pantallas',
                'icon' => 'images/categories/PANTALLAS.png',
                'description' => 'Monitores IPS, pantallas 4K para diseño, monitores curvos y soluciones profesionales.',
                'margin_percentage' => 17.0,
                'sort_order' => 6,
            ],
            [
                'name' => 'Computadores Servidores Y Notebooks',
                'slug' => 'computadores-servidores-y-notebooks',
                'icon' => 'images/categories/servidores.svg',
                'description' => 'Servidores en rack, blades y soluciones de infraestructura empresarial.',
                'margin_percentage' => 15.0,
                'sort_order' => 7,
            ],
            [
                'name' => 'Componentes De Sistema',
                'slug' => 'componentes-de-sistema',
                'icon' => 'images/categories/componentes.svg',
                'description' => 'Procesadores, placas madre, memorias y partes críticas para ensamblaje.',
                'margin_percentage' => 18.0,
                'sort_order' => 8,
            ],
            [
                'name' => 'Dispositivos De Entrada/Salida',
                'slug' => 'dispositivos-de-entradasalida',
                'icon' => 'images/categories/entradasalida.svg',
                'description' => 'Docks, hubs, interfaces y adaptadores multifunción para puestos de trabajo.',
                'margin_percentage' => 20.0,
                'sort_order' => 9,
            ],
            [
                'name' => 'Dispositivos De Red',
                'slug' => 'dispositivos-de-red',
                'icon' => 'images/categories/redes.svg',
                'description' => 'Routers empresariales, switches gestionados, puntos de acceso y conectividad.',
                'margin_percentage' => 18.0,
                'sort_order' => 10,
            ],
            [
                'name' => 'Software',
                'slug' => 'software',
                'icon' => 'images/categories/software.svg',
                'description' => 'Sistemas operativos Microsoft, licencias corporativas, suites y seguridad.',
                'margin_percentage' => 12.0,
                'sort_order' => 11,
            ],
            [
                'name' => 'Cables',
                'slug' => 'cables',
                'icon' => 'images/categories/cables.svg',
                'description' => 'Cables de red estructurado, fibra, HDMI de alta velocidad y alimentación.',
                'margin_percentage' => 25.0,
                'sort_order' => 12,
            ],
            [
                'name' => 'Suministros Y Medios',
                'slug' => 'suministros-y-medios',
                'icon' => 'images/categories/suministros.svg',
                'description' => 'Tóneres originales, tintas de alto rendimiento y medios de almacenamiento.',
                'margin_percentage' => 20.0,
                'sort_order' => 13,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 4. Seed Rich Technology Products
        $usdRate = 965.0;

        $productsData = [
            // Notebooks
            [
                'category_slug' => 'computadores-portatiles-tablets',
                'sku' => 'LEN-21EY000LCL',
                'ingram_part_number' => 'IM-LEN-21EY000LCL',
                'vendor_part_number' => '21EY000LCL',
                'name' => 'Lenovo ThinkPad E14 Gen 5 Core i7 16GB 512GB SSD 14" FHD IPS Win 11 Pro',
                'brand' => 'Lenovo',
                'short_description' => 'Notebook empresarial de alta resistencia militar MIL-SPEC con procesador Intel Core i7 de 13ª generación y lector de huellas.',
                'description' => '<p>El notebook Lenovo ThinkPad E14 Gen 5 está diseñado para empresas y profesionales que exigen fiabilidad y potencia. Cuenta con procesador Intel Core i7-1355U de 10 núcleos, 16GB de memoria DDR4 y un veloz SSD M.2 PCIe NVMe de 512GB.</p><h5>Rendimiento y Durabilidad Certificada</h5><p>Sometido a rigurosas pruebas de grado militar MIL-STD 810H contra caídas, polvo y temperaturas extremas. Su pantalla de 14 pulgadas FHD antirreflejo garantiza horas de confort visual.</p>',
                'cost_price_usd' => 820.00,
                'margin_percentage' => 15.00,
                'stock' => 14,
                'main_image' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Procesador' => 'Intel Core i7-1355U (hasta 5.0 GHz, 10 núcleos)',
                    'Memoria RAM' => '16 GB DDR4-3200 (expandible a 40 GB)',
                    'Almacenamiento' => '512 GB SSD M.2 2242 PCIe 4.0x4 NVMe',
                    'Pantalla' => '14" FHD (1920x1080) IPS 300 nits Antirreflejo',
                    'Gráficos' => 'Intel Iris Xe Graphics integrados',
                    'Sistema Operativo' => 'Windows 11 Pro 64-bit Español',
                    'Seguridad' => 'Chip TPM 2.0, Lector de Huella en botón de encendido',
                    'Garantía' => '3 años On-Site oficial Lenovo'
                ],
                'faqs' => [
                    ['question' => '¿Viene con Windows 11 Pro activado?', 'answer' => 'Sí, viene con licencia original de Windows 11 Pro OEM lista para unirse a dominios corporativos o Azure Active Directory.'],
                    ['question' => '¿Es posible ampliar la memoria RAM?', 'answer' => 'Sí, cuenta con un slot SODIMM libre para ampliar la memoria hasta un total de 40 GB.'],
                ]
            ],
            [
                'category_slug' => 'computadores-portatiles-tablets',
                'sku' => 'HP-7B6M2LA',
                'ingram_part_number' => 'IM-HP-7B6M2LA',
                'vendor_part_number' => '7B6M2LA#AKH',
                'name' => 'HP ProBook 450 G10 Core i5 1335U 16GB 512GB SSD 15.6" FHD Win 11 Pro',
                'brand' => 'HP',
                'short_description' => 'Portátil corporativo con chasis de aluminio, pantalla de 15.6" con teclado numérico dedicado y suite de seguridad HP Wolf Security.',
                'description' => '<p>El equipo HP ProBook 450 G10 proporciona a las empresas en crecimiento un rendimiento de grado comercial, seguridad multicapa HP Wolf Security for Business y durabilidad en un diseño compacto y actualizable.</p>',
                'cost_price_usd' => 690.00,
                'margin_percentage' => 14.50,
                'stock' => 22,
                'main_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Procesador' => 'Intel Core i5-1335U (10 núcleos, hasta 4.6 GHz)',
                    'Memoria RAM' => '16 GB DDR4-3200 MHz',
                    'Almacenamiento' => '512 GB PCIe NVMe SSD',
                    'Pantalla' => '15.6" diagonal FHD (1920 x 1080) antirreflejo',
                    'Teclado' => 'Teclado numérico resistente a derrames',
                    'Sistema Operativo' => 'Windows 11 Pro'
                ],
                'faqs' => [
                    ['question' => '¿Incluye puerto de red RJ-45 cableado?', 'answer' => 'Sí, incorpora puerto Gigabit Ethernet RJ-45 nativo, además de Wi-Fi 6E y Bluetooth 5.3.']
                ]
            ],

            // Desktop PCs
            [
                'category_slug' => 'computadores-de-escritorio',
                'sku' => 'DEL-OPT7010SFF',
                'ingram_part_number' => 'IM-DEL-OPT7010SFF',
                'vendor_part_number' => 'OPT-7010-SFF',
                'name' => 'Dell OptiPlex Small Form Factor 7010 Core i7 16GB 512GB SSD Win 11 Pro',
                'brand' => 'Dell',
                'short_description' => 'Computador de escritorio corporativo de formato compacto SFF, alta eficiencia energética y máxima estabilidad.',
                'description' => '<p>OptiPlex 7010 de factor de forma reducido (SFF) ofrece una productividad inteligente y confiable con procesadores Intel Core i7 de 13ª generación. Diseñado para optimizar el espacio de trabajo en empresas e instituciones.</p>',
                'cost_price_usd' => 740.00,
                'margin_percentage' => 16.00,
                'stock' => 9,
                'main_image' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Procesador' => 'Intel Core i7-13700 (16 núcleos, 24 hilos, hasta 5.2 GHz)',
                    'Memoria RAM' => '16 GB DDR4 3200MHz',
                    'Almacenamiento' => '512 GB M.2 NVMe SSD',
                    'Formato' => 'Small Form Factor (SFF) ultra compacto',
                    'Conectividad' => 'DisplayPort 1.4a, HDMI, USB 3.2 Gen 1, RJ-45 Gigabit',
                    'Sistema Operativo' => 'Windows 11 Pro 64-bit'
                ],
                'faqs' => [
                    ['question' => '¿Incluye mouse y teclado?', 'answer' => 'Sí, incluye teclado USB corporativo Dell en español y mouse óptico USB original Dell.'],
                    ['question' => '¿Cuántos monitores simultáneos soporta?', 'answer' => 'Soporta hasta 3 pantallas digitales simultáneas mediante sus salidas DisplayPort y HDMI sin necesidad de tarjeta de video adicional.']
                ]
            ],
            [
                'category_slug' => 'computadores-de-escritorio',
                'sku' => 'HP-ELITEDESK-800',
                'ingram_part_number' => 'IM-HP-800G9TWR',
                'vendor_part_number' => '6D380LT#ABM',
                'name' => 'HP Elite Tower 800 G9 Core i7 32GB 1TB NVMe RTX 3060 Win 11 Pro',
                'brand' => 'HP',
                'short_description' => 'Workstation de torre de máximo rendimiento para diseño CAD, desarrollo de software y computación intensiva.',
                'description' => '<p>La torre HP Elite 800 G9 ofrece el rendimiento que exigen los usuarios avanzados. Diseñada para cargas pesadas con tarjeta gráfica dedicada NVIDIA GeForce RTX 3060 y 32GB de memoria RAM.</p>',
                'cost_price_usd' => 1350.00,
                'margin_percentage' => 18.00,
                'stock' => 5,
                'main_image' => 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Procesador' => 'Intel Core i7-13700 vPro',
                    'Memoria' => '32 GB DDR5-4800 MHz',
                    'Disco Duro' => '1 TB PCIe NVMe TLC SSD M.2',
                    'Tarjeta Gráfica' => 'NVIDIA GeForce RTX 3060 12GB GDDR6',
                    'Fuente de Poder' => '500 W 92% de eficiencia 80 PLUS Platinum'
                ]
            ],

            // SSD Storage
            [
                'category_slug' => 'discos-de-estado-solido-ssd',
                'sku' => 'KNG-SKC3000S-1024G',
                'ingram_part_number' => 'IM-KNG-SKC3000S-1024G',
                'vendor_part_number' => 'SKC3000S/1024G',
                'name' => 'Kingston KC3000 SSD 1TB M.2 2280 PCIe 4.0 NVMe (Hasta 7000 MB/s)',
                'brand' => 'Kingston',
                'short_description' => 'Unidad de estado sólido PCIe 4.0 NVMe para servidores, estaciones de trabajo y usuarios que requieren velocidades extremas.',
                'description' => '<p>Kingston KC3000 PCIe 4.0 NVMe M.2 SSD ofrece un rendimiento de nivel superior con el más reciente controlador Gen 4x4 NVMe y 3D TLC NAND. Con velocidades de lectura de hasta 7.000 MB/s y escritura de 6.000 MB/s.</p>',
                'cost_price_usd' => 84.00,
                'margin_percentage' => 22.00,
                'stock' => 45,
                'main_image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Capacidad' => '1024 GB (1 TB)',
                    'Interfaz' => 'PCIe 4.0 x4 NVMe',
                    'Velocidad de Lectura' => 'Hasta 7.000 MB/s',
                    'Velocidad de Escritura' => 'Hasta 6.000 MB/s',
                    'Disipador' => 'Difusor de calor de aluminio de grafeno de bajo perfil',
                    'Garantía' => '5 años limitada con asistencia técnica gratuita'
                ],
                'faqs' => [
                    ['question' => '¿Es compatible con PlayStation 5?', 'answer' => 'Sí, cumple y supera con creces las especificaciones exigidas por Sony para la expansión de almacenamiento en PS5.'],
                ]
            ],
            [
                'category_slug' => 'discos-de-estado-solido-ssd',
                'sku' => 'CRU-CT1000P3SSD8',
                'ingram_part_number' => 'IM-CRU-CT1000P3SSD8',
                'vendor_part_number' => 'CT1000P3SSD8',
                'name' => 'Crucial P3 1TB M.2 PCIe 3.0 NVMe 3500 MB/s',
                'brand' => 'Crucial',
                'short_description' => 'Actualización de almacenamiento de alta fiabilidad y excelente costo-beneficio para portátiles y PCs.',
                'cost_price_usd' => 58.00,
                'margin_percentage' => 24.00,
                'stock' => 60,
                'main_image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=800&auto=format&fit=crop&q=80',
                'specifications' => [
                    'Capacidad' => '1 TB',
                    'Lectura Secuencial' => '3500 MB/s',
                    'Escritura Secuencial' => '3000 MB/s',
                    'Formato' => 'M.2 2280'
                ]
            ],

            // Peripherals
            [
                'category_slug' => 'dispositivos-de-entrada',
                'sku' => 'LOG-920-009235',
                'ingram_part_number' => 'IM-LOG-920-009235',
                'vendor_part_number' => '920-009235',
                'name' => 'Logitech MX Keys Advanced Teclado Inalámbrico Iluminado Bluetooth / Bolt',
                'brand' => 'Logitech',
                'short_description' => 'Teclado inalámbrico premium con teclas cóncavas para escritura fluida, retroiluminación inteligente y recarga USB-C.',
                'description' => '<p>Presentamos MX Keys, el teclado avanzado para programadores, creativos y ejecutivos. Teclas esféricas cóncavas moldeadas a la forma de las yemas de los dedos, sensores de proximidad de manos e iluminación adaptable.</p>',
                'cost_price_usd' => 88.00,
                'margin_percentage' => 26.00,
                'stock' => 28,
                'main_image' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Conectividad' => 'Bluetooth Low Energy y receptor USB Logi Bolt',
                    'Multi-dispositivo' => 'Conmutación Easy-Switch entre 3 dispositivos simultáneos',
                    'Batería' => 'Recargable Li-Po (1500 mAh) vía USB-C, hasta 5 meses con luz apagada',
                    'Compatibilidad' => 'Windows, macOS, Linux, ChromeOS, iPadOS, Android'
                ],
                'faqs' => [
                    ['question' => '¿Tiene distribución en español con letra Ñ física?', 'answer' => 'Sí, todas nuestras unidades son distribución oficial para Latinoamérica con la tecla Ñ física grabada.'],
                ]
            ],
            [
                'category_slug' => 'dispositivos-de-entrada',
                'sku' => 'LOG-910-005692',
                'ingram_part_number' => 'IM-LOG-910-005692',
                'vendor_part_number' => '910-005692',
                'name' => 'Logitech MX Master 3S Mouse Inalámbrico de Precisión 8K DPI Sensor Silencioso',
                'brand' => 'Logitech',
                'short_description' => 'El ratón más icónico y productivo del mundo con sensor óptico de 8000 DPI sobre cualquier superficie y clics ultra silenciosos.',
                'cost_price_usd' => 76.00,
                'margin_percentage' => 25.00,
                'stock' => 35,
                'main_image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Sensor' => 'Darkfield de alta precisión de 8.000 DPI (funciona incluso sobre cristal)',
                    'Rueda de desplazamiento' => 'MagSpeed electromagnética ultra rápida y precisa',
                    'Clicks' => 'Quiet Clicks (90% menos de ruido)'
                ]
            ],

            // Displays / Screens
            [
                'category_slug' => 'pantallas',
                'sku' => 'DEL-P2723D',
                'ingram_part_number' => 'IM-DEL-P2723D',
                'vendor_part_number' => 'P2723D',
                'name' => 'Monitor Dell Profesional P2723D 27" QHD (2560 x 1440) IPS ComfortView Plus',
                'brand' => 'Dell',
                'short_description' => 'Monitor corporativo QHD de 27 pulgadas con bisel ultrafino, base ergonómica regulable en altura y tecnología ComfortView Plus.',
                'description' => '<p>Mantén la productividad sin importar dónde trabajes con este monitor QHD de 27 pulgadas con resolución 2560x1440, excelente cobertura de color sRGB del 99% y reducción de emisiones de luz azul sin alterar la fidelidad del color.</p>',
                'cost_price_usd' => 245.00,
                'margin_percentage' => 17.50,
                'stock' => 18,
                'main_image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Tamaño' => '27 pulgadas',
                    'Resolución' => 'QHD (2560 x 1440) a 60 Hz',
                    'Panel' => 'IPS con acabado antirreflejo 3H',
                    'Ajustes de base' => 'Altura, inclinación, giro y rotación 90° (Pivot)',
                    'Puertos' => '1x HDMI 1.4, 1x DisplayPort 1.2, 4x USB 3.2 Gen 1 downstream'
                ],
                'faqs' => [
                    ['question' => '¿Incluye los cables necesarios para conectar al computador?', 'answer' => 'Sí, incluye en la caja cable DisplayPort a DisplayPort original y cable USB de subida.']
                ]
            ],
            [
                'category_slug' => 'pantallas',
                'sku' => 'ASU-PA278CV',
                'ingram_part_number' => 'IM-ASU-PA278CV',
                'vendor_part_number' => 'PA278CV',
                'name' => 'ASUS ProArt Display PA278CV 27" WQHD IPS 100% sRGB Calman Verified USB-C',
                'brand' => 'ASUS',
                'short_description' => 'Monitor profesional calibrado de fábrica Delta E < 2 con puerto USB-C con suministro de energía de 65W.',
                'cost_price_usd' => 310.00,
                'margin_percentage' => 18.00,
                'stock' => 8,
                'main_image' => 'https://images.unsplash.com/photo-1547119957-637f8679db1e?w=800&auto=format&fit=crop&q=80',
                'specifications' => [
                    'Resolución' => 'WQHD (2560 x 1440)',
                    'Precisión de Color' => '100% sRGB, 100% Rec. 709, Calman Verified',
                    'USB-C' => 'Video, datos y carga rápida de 65W a través de un solo cable'
                ]
            ],

            // Printers
            [
                'category_slug' => 'impresoras',
                'sku' => 'EPS-C11CJ67301',
                'ingram_part_number' => 'IM-EPS-C11CJ67301',
                'vendor_part_number' => 'C11CJ67301',
                'name' => 'Epson EcoTank L5590 Multifuncional Tinta Continua Wi-Fi ADF Fax Red Ethernet',
                'brand' => 'Epson',
                'short_description' => 'Impresora multifuncional 4 en 1 para oficinas y negocios con alimentador automático ADF y costo de impresión ultra bajo.',
                'description' => '<p>La impresora multifuncional inalámbrica EcoTank L5590 es ideal para pymes y grupos de trabajo. Cuenta con tecnología Heat-Free de Epson, impresión a doble cara manual, escaneo por alimentador ADF y conectividad completa.</p>',
                'cost_price_usd' => 220.00,
                'margin_percentage' => 18.00,
                'stock' => 12,
                'main_image' => 'https://images.unsplash.com/photo-1612815154858-60aa4c59eaa6?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'specifications' => [
                    'Funciones' => 'Impresión, copiado, escaneado y fax',
                    'Rendimiento de Tinta' => 'Hasta 4.300 páginas en negro y 7.300 páginas en color por juego de botellas',
                    'Alimentador ADF' => 'Capacidad de 30 páginas para digitalización rápida',
                    'Conexiones' => 'Wi-Fi Direct, Ethernet 10/100, USB 2.0 de alta velocidad'
                ],
                'faqs' => [
                    ['question' => '¿Incluye las botellas de tinta originales en la caja?', 'answer' => 'Sí, incluye el juego completo de botellas de tinta original Epson (Negro, Cian, Magenta, Amarillo) para empezar a imprimir de inmediato.']
                ]
            ],
            [
                'category_slug' => 'impresoras',
                'sku' => 'BRO-HL-L2360DW',
                'ingram_part_number' => 'IM-BRO-HLL2360DW',
                'vendor_part_number' => 'HLL2360DW',
                'name' => 'Brother HL-L2360DW Impresora Láser Monocromática Dúplex Automático Wi-Fi',
                'brand' => 'Brother',
                'short_description' => 'Impresora láser monocromática compacta de alta velocidad (32 ppm) con impresión automática a doble cara y bajo costo por página.',
                'cost_price_usd' => 140.00,
                'margin_percentage' => 19.00,
                'stock' => 16,
                'main_image' => 'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?w=800&auto=format&fit=crop&q=80',
                'specifications' => [
                    'Velocidad de Impresión' => 'Hasta 32 páginas por minuto',
                    'Doble Cara' => 'Dúplex automático estándar',
                    'Bandeja de Papel' => '250 hojas'
                ]
            ],
        ];

        foreach ($productsData as $item) {
            $cat = $categories[$item['category_slug']] ?? null;
            $catId = $cat ? $cat->id : null;

            $costUsd = (float) $item['cost_price_usd'];
            $margin = (float) ($item['margin_percentage'] ?? ($cat ? $cat->margin_percentage : 18.0));
            $costClp = $costUsd * $usdRate;
            $retailClp = round(($costClp * (1 + ($margin / 100))) / 100) * 100;

            Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'category_id' => $catId,
                    'ingram_part_number' => $item['ingram_part_number'] ?? null,
                    'vendor_part_number' => $item['vendor_part_number'] ?? null,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name'] . '-' . $item['sku']),
                    'brand' => $item['brand'] ?? 'INEXUS',
                    'short_description' => $item['short_description'] ?? null,
                    'description' => $item['description'] ?? '<p>' . ($item['short_description'] ?? '') . '</p>',
                    'specifications' => $item['specifications'] ?? [],
                    'faqs' => $item['faqs'] ?? [],
                    'cost_price_usd' => $costUsd,
                    'cost_price_clp' => $costClp,
                    'margin_percentage' => $item['margin_percentage'] ?? null,
                    'calculated_price_clp' => $retailClp,
                    'regular_price' => $retailClp,
                    'stock' => $item['stock'] ?? 10,
                    'stock_status' => ($item['stock'] ?? 10) > 0 ? 'in_stock' : 'out_of_stock',
                    'main_image' => $item['main_image'] ?? null,
                    'scraper_source' => 'spdigital',
                    'scraper_status' => 'found',
                    'scraper_last_run' => now(),
                    'is_featured' => $item['is_featured'] ?? false,
                    'is_active' => true,
                    'meta_title' => $item['name'] . ' | INEXUS Chile',
                    'meta_description' => Str::limit(strip_tags($item['short_description'] ?? $item['name']), 155),
                ]
            );
        }
    }
}
