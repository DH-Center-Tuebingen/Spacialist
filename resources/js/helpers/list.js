import { getObjectValueByArray, setObjectValueByArray } from "./object";


export function moveItem(array, from, to, rankPath = "pivot.position") {
    if(from === to) {
        return;
    }

    let increment = from < to ? 1 : -1;
    const fromItem = array[from];
    const toItem = array[to];
    array.splice(from, 1);
    

    const toItemRank = getObjectValueByArray(toItem, rankPath)    
    setObjectValueByArray(fromItem, rankPath, toItemRank)
    setObjectValueByArray(toItem, rankPath, toItemRank)
    array.splice(to, 0, fromItem);

    let updatedRank = toItemRank;
    for(let i = to; i !== (from - increment); i -= increment) {
        setObjectValueByArray(array[i], rankPath, updatedRank);
        updatedRank -= increment;
    }
    
    return toItemRank;
}