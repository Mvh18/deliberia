<?php

namespace App\Integrations\Metadata;

use RuntimeException;

/**
 * El proveedor no pudo devolver metadatos (fallo de red, cuota, no encontrado o desactivado).
 */
class MetadataUnavailable extends RuntimeException {}
