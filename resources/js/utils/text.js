// Quita tildes/diacríticos para que la búsqueda encuentre "frances" al buscar "francés".
export function normalizeText(value) {
  return (value || '')
    .toString()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
}
