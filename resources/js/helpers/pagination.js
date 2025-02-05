export function isPaginated(data) {
    return data.hasOwnProperty('current_page');
}