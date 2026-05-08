#!/usr/bin/env bash
set -euo pipefail

# Uso:
# ./convertir-webp.sh [origen] [destino] [calidad]
#
# calidad (opcional): 0-100
# Recomendado para máxima optimización sin destrozar imagen: 65-75

SRC_DIR="${1:-/Volumes/RAID/Fotos_WebP}"
DST_DIR="${2:-/Volumes/RAID/Fotos_WebP_convertidas}"
QUALITY="${3:-72}"

if ! command -v magick >/dev/null 2>&1; then
  echo "ERROR: ImageMagick no está instalado. Instala con: brew install imagemagick"
  exit 1
fi

mkdir -p "$DST_DIR"

# Normaliza nombres para base de datos/URLs:
# - minúsculas
# - sin tildes/ñ/diacríticos
# - solo [a-z0-9._-]
# - espacios y separadores -> "-"
slugify_name() {
  local input="$1"
  local out

  # Quita acentos/diacríticos y pasa a ASCII
  out="$(printf '%s' "$input" | iconv -f UTF-8 -t ASCII//TRANSLIT 2>/dev/null || printf '%s' "$input")"
  # Minúsculas
  out="$(printf '%s' "$out" | tr '[:upper:]' '[:lower:]')"
  # Separadores comunes a guion
  out="$(printf '%s' "$out" | sed -E 's/[[:space:]]+/-/g; s/[+&]+/-/g; s/[\/\\]+/-/g')"
  # Dejar solo caracteres seguros
  out="$(printf '%s' "$out" | sed -E 's/[^a-z0-9._-]+/-/g')"
  # Compactar guiones y limpiar bordes
  out="$(printf '%s' "$out" | sed -E 's/-+/-/g; s/^[-._]+//; s/[-._]+$//')"

  if [[ -z "$out" ]]; then
    out="imagen"
  fi

  printf '%s' "$out"
}

# Buscar imágenes compatibles
mapfile -d '' FILES < <(
  find "$SRC_DIR" -type f \( \
    -iname "*.jpg" -o -iname "*.jpeg" -o -iname "*.png" -o \
    -iname "*.tif" -o -iname "*.tiff" -o -iname "*.psd" \
  \) -print0
)

TOTAL="${#FILES[@]}"
if [[ "$TOTAL" -eq 0 ]]; then
  echo "No se encontraron imágenes en: $SRC_DIR"
  exit 0
fi

echo "Origen:   $SRC_DIR"
echo "Destino:  $DST_DIR"
echo "Calidad:  $QUALITY"
echo "Archivos: $TOTAL"
echo

i=0
for file in "${FILES[@]}"; do
  ((i+=1))

  # Ruta relativa para conservar estructura de carpetas
  rel="${file#$SRC_DIR/}"
  rel_dir="$(dirname "$rel")"
  base_name="$(basename "$rel")"
  base_no_ext="${base_name%.*}"
  safe_base="$(slugify_name "$base_no_ext")"

  # También saneamos cada carpeta del path relativo
  safe_rel_dir=""
  IFS='/' read -r -a dir_parts <<< "$rel_dir"
  for part in "${dir_parts[@]}"; do
    [[ -z "$part" || "$part" == "." ]] && continue
    safe_part="$(slugify_name "$part")"
    if [[ -z "$safe_rel_dir" ]]; then
      safe_rel_dir="$safe_part"
    else
      safe_rel_dir="$safe_rel_dir/$safe_part"
    fi
  done

  if [[ -n "$safe_rel_dir" ]]; then
    out="$DST_DIR/$safe_rel_dir/$safe_base.webp"
  else
    out="$DST_DIR/$safe_base.webp"
  fi

  mkdir -p "$(dirname "$out")"

  # -auto-orient: corrige orientación EXIF
  # -strip: elimina metadatos pesados
  # webp:method=6: compresión más agresiva (más lenta, menor peso)
  magick "$file" \
    -auto-orient \
    -strip \
    -define webp:method=6 \
    -quality "$QUALITY" \
    "$out"

  pct=$(( i * 100 / TOTAL ))
  printf "[%4d/%4d] %3d%% %s\n" "$i" "$TOTAL" "$pct" "$out"
done

echo
echo "Listo. Conversión terminada."
