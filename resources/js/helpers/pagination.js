export function isPaginated(data) {
    return data.hasOwnProperty('current_page');
}

export function hasNextPage(data) {
    return !!data.next_page_url;
}