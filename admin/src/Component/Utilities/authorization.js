// function to search for a given permission
// todo : refactor with authProvider function
export default function isAuthorized(action) {
    let permissions = JSON.parse(localStorage.getItem('permissions'));
    return permissions.hasOwnProperty(action);
}