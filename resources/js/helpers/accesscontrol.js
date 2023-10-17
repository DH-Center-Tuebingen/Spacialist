import {
    fetchAccessGroups,
} from '@/api/accesscontrol.js';

export async function getAccessGroups() {
    const groups = await fetchAccessGroups();
    return groups;
}