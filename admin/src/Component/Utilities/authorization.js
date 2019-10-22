// function to search for a given permission
// todo : refactor with authProvider function
export default function isAuthorized(action) {
    if (localStorage.getItem('permissions')) {
        let permissions = JSON.parse(localStorage.getItem('permissions'));
        return permissions.hasOwnProperty(action);
    }
    return false;
}