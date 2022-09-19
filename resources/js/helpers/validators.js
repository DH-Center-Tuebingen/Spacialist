// Validators
export function $validateObject(value) {
    // concepts is valid if it is either an object
    // or an empty array
    // (empty assoc arrays are simple arrays in php)
    return typeof value == 'object' || (typeof value == 'array' && value.length == 0);
};

export function isValidOrcid(oid) {
    if(oid === '') {
        return false;
    }
    if(/^\d{15}[0-9Xx]$/.test(oid)) {
        //
    } else if(/^\d{4}-\d{4}-\d{4}-\d{3}[0-9Xx]$/.test(oid)) {
        oid = oid.replaceAll('-', '');
    } else {
        return false;
    }
    let tot = 0;
    for(let i=0; i<oid.length-1; i++) {
        let val = Number.parseInt(oid[i]);
        tot = (tot+val) * 2;
    }
    let chk = (12 - (tot % 11)) % 11;
    if(chk == 10) chk = 'X';
    return oid[oid.length-1].toUpperCase() == chk;
};
