#!/usr/bin/env bash
set -euo pipefail

# Uso:
# ./convertir-webp.sh [origen] [destino] [calidad] [preset] [color_mode]
#
# calidad (opcional): 0-100
# preset (opcional): balanced | high | small
# - balanced: equilibrio calidad/peso (por defecto)
# - high: más calidad visual, peso contenido
# - small: más compresión, menor peso
# color_mode (opcional): preserve | srgb
# - preserve: conserva el perfil de color original (por defecto)
# - srgb: convierte a sRGB para web manteniendo color estable

SRC_DIR="${1:-/Volumes/RAID/Fotos_WebP}"
DST_DIR="${2:-/Volumes/RAID/Fotos_WebP_convertidas}"
QUALITY="${3:-76}"
PRESET="${4:-balanced}"
COLOR_MODE="${5:-preserve}"

if ! command -v magick >/dev/null 2>&1; then
  echo "ERROR: ImageMagick no está instalado. Instala con: brew install imagemagick"
  exit 1
fi

mkdir -p "$DST_DIR"

if ! [[ "$QUALITY" =~ ^[0-9]+$ ]] || (( QUALITY < 0 || QUALITY > 100 )); then
  echo "ERROR: calidad inválida '$QUALITY'. Usa un número entre 0 y 100."
  exit 1
fi

if [[ "$PRESET" != "balanced" && "$PRESET" != "high" && "$PRESET" != "small" ]]; then
  echo "ERROR: preset inválido '$PRESET'. Usa: balanced | high | small"
  exit 1
fi

if [[ "$COLOR_MODE" != "preserve" && "$COLOR_MODE" != "srgb" ]]; then
  echo "ERROR: color_mode inválido '$COLOR_MODE'. Usa: preserve | srgb"
  exit 1
fi

# Parámetros de WebP por preset
# (un preset es un “modo preconfigurado”)
WEBP_METHOD=6
WEBP_SNS=58
WEBP_FILTER=18
WEBP_SHARPNESS=3
WEBP_SEGMENTS=4
WEBP_PASS=6
WEBP_SHARP_YUV=true
WEBP_AUTO_FILTER=true

case "$PRESET" in
  high)
    WEBP_SNS=62
    WEBP_FILTER=18
    WEBP_SHARPNESS=3
    WEBP_PASS=8
    ;;
  balanced)
    WEBP_SNS=58
    WEBP_FILTER=18
    WEBP_SHARPNESS=3
    WEBP_PASS=6
    ;;
  small)
    WEBP_SNS=48
    WEBP_FILTER=16
    WEBP_SHARPNESS=2
    WEBP_PASS=4
    ;;
esac

if [[ "$COLOR_MODE" == "preserve" ]]; then
  # Evita contraste extra cuando queremos fidelidad al color original.
  WEBP_SHARP_YUV=false
  WEBP_AUTO_FILTER=false
fi

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
echo "Preset:   $PRESET"
echo "Color:    $COLOR_MODE"
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
  # preserve: mantiene perfil ICC (evita cambios de color no deseados)
  # srgb: normaliza a sRGB para web
  # use-sharp-yuv/autofilter/sns/filter/pass: mejora detalle visual sin disparar peso
  if [[ "$COLOR_MODE" == "srgb" ]]; then
    magick "$file" \
      -auto-orient \
      -colorspace sRGB \
      -define webp:method="$WEBP_METHOD" \
      -define webp:use-sharp-yuv="$WEBP_SHARP_YUV" \
      -define webp:auto-filter="$WEBP_AUTO_FILTER" \
      -define webp:sns-strength="$WEBP_SNS" \
      -define webp:filter-strength="$WEBP_FILTER" \
      -define webp:filter-sharpness="$WEBP_SHARPNESS" \
      -define webp:segments="$WEBP_SEGMENTS" \
      -define webp:pass="$WEBP_PASS" \
      -quality "$QUALITY" \
      "$out"
  else
    magick "$file" \
      -auto-orient \
      -define webp:method="$WEBP_METHOD" \
      -define webp:use-sharp-yuv="$WEBP_SHARP_YUV" \
      -define webp:auto-filter="$WEBP_AUTO_FILTER" \
      -define webp:sns-strength="$WEBP_SNS" \
      -define webp:filter-strength="$WEBP_FILTER" \
      -define webp:filter-sharpness="$WEBP_SHARPNESS" \
      -define webp:segments="$WEBP_SEGMENTS" \
      -define webp:pass="$WEBP_PASS" \
      -quality "$QUALITY" \
      "$out"
  fi

  pct=$(( i * 100 / TOTAL ))
  printf "[%4d/%4d] %3d%% %s\n" "$i" "$TOTAL" "$pct" "$out"
done

echo
echo "Listo. Conversión terminada."
