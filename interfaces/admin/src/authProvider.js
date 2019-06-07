import { AUTH_LOGIN, AUTH_LOGOUT, AUTH_ERROR, AUTH_CHECK } from 'react-admin';
import decodeJwt from 'jwt-decode';
import { AUTH_GET_PERMISSIONS } from 'ra-core';

require('dotenv').config();

// Change this to be your own authentication token URI.
const authenticationTokenUri = process.env.REACT_APP_API_LOGIN;
//const permissionsUri = process.env.REACT_APP_API_PERMISSIONS;

export default (type, params) => {
    switch (type) {
        case AUTH_LOGIN:
            const { username, password } = params;
            const request = new Request(authenticationTokenUri, {
                method: 'POST',
                body: JSON.stringify({ username: username, password: password }),
                headers: new Headers({ 'Content-Type': 'application/json' }),
            });

            return fetch(request)
                .then(response => {
                    if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);
                    return response.json();
                })
                .then(({ token }) => {
                    const decodedToken = decodeJwt(token);
                    var authorized = decodedToken.roles.find(function(element) {
                        return (element === 'ROLE_ADMIN' || element === 'ROLE_SUPER_ADMIN');
                    });
                    if (!authorized) throw new Error('Unauthorized');
                    localStorage.setItem('token', token); // The JWT token is stored in the browser's local storage
                    localStorage.setItem('roles', decodedToken.roles);
                    localStorage.setItem('id', decodedToken.id);
                });

        case AUTH_LOGOUT:
            localStorage.removeItem('token');
            localStorage.removeItem('roles');
            localStorage.removeItem('id');
            return Promise.resolve();

        case AUTH_ERROR:
            if (401 === params.response.status || 403 === params.response.status) {
                localStorage.removeItem('token');
                localStorage.removeItem('roles');
                localStorage.removeItem('id');
                return Promise.reject();
            }
            return Promise.resolve();

        case AUTH_CHECK:
            return localStorage.getItem('token') ? Promise.resolve() : Promise.reject({ redirectTo: '/login' });

        case AUTH_GET_PERMISSIONS:
            // call to the permission ressource
            let action = null;
            const roles = localStorage.getItem('roles');
            if (params && params.location) {

                switch (params.location) {
                    case "/users":
                        action = "user_manage";
                        return true;
                    case "/communities":
                        action = "community_manage";    
                        return true;
                    case "/roles":
                        return roles.find(function(element) {
                            return (element === 'ROLE_SUPER_ADMIN');
                        });
                    case "/rights":
                        return roles.find(function(element) {
                            return (element === 'ROLE_SUPER_ADMIN');
                        });    
                    case "/relay_points":break;
                    case "/relay_point_types":break;
                    case "/community_users":break;
                    case "/articles":break;
                    case "/sections":break;
                    case "/territories":break;
                    default:break;
                }
            } else {
                return true;
            }
            return false;

            // const request = new Request(permissionsUri, {
            //     method: 'GET',
            //     headers: new Headers({ 'Content-Type': 'application/json' }),
            // });

            // return fetch(request)
            //     .then(response => {
            //         if (response.status < 200 || response.status >= 300) throw new Error(response.statusText);
            //         return response.json();
            //     });
            // const roles = localStorage.getItem('roles');
            // return roles ? Promise.resolve(roles) : Promise.reject();

        default:
            return Promise.resolve();
    }
}