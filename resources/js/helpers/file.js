export default class File {

    /**
     * Save a file from a response object by providing a  fallback name,
     * otherwise the sent filename will be used. 
     * 
     * This is the same as using the download function but can be directly hooked into a .then() block of an axios request
     * 
     * @param {*} fallbackFilename 
     * @returns {Function}
     */
    static saveFileWithFallback(fallbackFilename) {
        return (response) => {
            this.download(response, fallbackFilename);
        };
    }

    /**
     * Download a file from a response object by providing a fallback name,
     * otherwise the sent filename will be used.
     * 
     * @param {AxiosResponse} response 
     * @param {string} fallbackFilename 
     */
    static download(response, fallbackFilename) {
        const blob = new Blob([response.data], { type: response.data.type });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const fileName = this.getFilename(response, fallbackFilename);
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    }

    /**
     * Get the filename from the response headers or use a fallback name
     * 
     * @param {AxiosResponse} response
     * @param {string} fallbackFilename
     * @returns {string}
     */
    static getFilename(response, fallbackFilename) {
        let filename = fallbackFilename;
        const contentDisposition = response.headers['content-disposition'];
        if(contentDisposition) {
            const fileNameMatch = contentDisposition.match(/filename=(.+)/);
            if(fileNameMatch && fileNameMatch.length === 2) {
                filename = fileNameMatch[1];
            }
        }
        return filename;
    }
}