import React from "react";
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';
import { fetchHydra as baseFetchHydra  } from '@api-platform/admin';
import baseDataProvider from '@api-platform/admin/lib/hydra/dataProvider';

import { Redirect } from 'react-router-dom';
import { fetchUtils } from 'react-admin';

const entrypoint = process.env.REACT_APP_API;
const token = localStorage.getItem('token');
const apiUrlCreateUSer = process.env.REACT_APP_API+process.env.REACT_APP_CREATE_USER;
const httpClient = fetchUtils.fetchJson;
const currentUser =  localStorage.getItem('id');

const fetchHeaders = function () {
    return {'Authorization': `Bearer ${localStorage.getItem('token')}`};
};
const fetchHydra = (url, options = {}) => baseFetchHydra(url, {
    ...options,
    headers: new Headers(fetchHeaders()),
});

const apiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint, { headers: new Headers(fetchHeaders()) })
    .then(
        ({ api }) => ({api}),
        (result) => {
            switch (result.status) {
                case 401:
                    return Promise.resolve({
                        api: result.api,
                        customRoutes: [{
                            props: {
                                path: '/',
                                render: () => <Redirect to={`/login`}/>,
                            },
                        }],
                    });

                default:
                    return Promise.reject(result);
            }
        },
    );
const dataProvider = baseDataProvider(entrypoint, fetchHydra, apiDocumentationParser);


const myDataProvider = {
    ...dataProvider,
    create: (resource, params) => {
        if (resource !== 'users') {
            // fallback to the default implementation
            return dataProvider.create(resource, params);
        }else{
            const options = {}
            if (!options.headers) {
                options.headers = new Headers({ Accept: 'application/json' });
            }
            options.headers.set('Authorization', `Bearer ${token}`);

            /* Rewrite roles for fit with api */
            let newRoles = []
            console.info(params.data.fields)
            params.data.fields.forEach(function(v){
                  var territory = v.territory;
                  //There is many roles
                  if (Array.isArray(v.roles.isArray) ){
                    v.roles.forEach(function(r){
                      v != null ?  newRoles.push({"authItem": r, "territory": territory}) :   newRoles.push({"authItem": r});
                    });
                  //There is just 1 roles
                }else{
                  v != null ?  newRoles.push({"authItem": v.roles, "territory": territory}) :   newRoles.push({"authItem": v.roles});
                }
            });
            params.data.userAuthAssignments = newRoles
            /* Rewrite roles for fit with api */

            /* Rewrite adresse for API */
            params.data.addresses =  new Array();
            params.data.addresses[0] = params.data.address
            params.data.addresses[0].home = true

            /* Add custom fields fo fit with api */
            params.data.passwordSendType = 1
            params.data.language = "fr_FR"
            params.data.userDelegate= "/users/"+currentUser;
            /* Add custom fields fo fit with api */

            return httpClient(`${apiUrlCreateUSer}`, {
                method: 'POST',
                body: JSON.stringify(params.data),
                headers : options.headers
            }).then(({ json }) => ({
                data: { ...params.data, id: json.id },

            }))

        }
    },
    getOne: (resource, params) => {
        if (resource !== 'users') {
            // fallback to the default implementation
            return dataProvider.getOne(resource, params);
        }else{

          var lid = params.id.search("users") == -1 ? "users/"+params.id : params.id;

          return dataProvider.getOne('users',{id:lid} )
              .then(  ({ data } )  =>
                  Promise.all(data.userAuthAssignments.map(element =>

                      dataProvider.getOne('userAuthAssignments',{id: element} )
                          .then( ({ data }) => data )
                          .catch( error => {
                              console.log("Erreur lors de la récupération des droits:", error)
                          })
                        )
                  ).then(
                    // We fill the array rolesTerritory with good format for admin
                    dataThen  =>  {
                        data.rolesTerritory = dataThen.reduce( (acc,val) => {
                          var territory =  val.territory == null ? 'null' : val.territory ;

                            if(!acc[territory]){
                              acc[territory] = [];
                            }
                            acc[territory].push(val.authItem);
                            return acc;
                        }
                          , {}  )
                        return {data};
                    }
                  )
              );
          }
    },
    getList : (resource, params) => {
        if (resource == 'communities') {
            //Add a the custom filter : Admin, so we can have full control of resultats in API side
            resource = resource + '/accesFromAdminReact';

        }
        return dataProvider.getList(resource, params);

    },
    update: (resource, params) => {
        if (resource !== 'users') {
            // fallback to the default implementation
            return dataProvider.update(resource, params);
        }else{
          const options = {}
          if (!options.headers) {
              options.headers = new Headers({ Accept: 'application/json' });
          }
          options.headers.set('Authorization', `Bearer ${token}`);

          /* Rewrite roles for fit with api */
          let newRoles = []
          if (  params.data.fields != null ){
            params.data.fields.forEach(function(v){
                  var territory = v.territory;
                  v.roles.forEach(function(r){
                    v != null ?  newRoles.push({"authItem": r, "territory": territory}) :   newRoles.push({"authItem": r});
                  });
            });
          }else{
            for (const territory in  params.data.rolesTerritory) {
              for (const r in  params.data.rolesTerritory[territory]) {
                const role = params.data.rolesTerritory[territory][r]
                  territory != null ?  newRoles.push({"authItem": role, "territory": territory}) :   newRoles.push({"authItem": role});
              }
          }
        }

          params.data.userAuthAssignments = newRoles
          /* Rewrite roles for fit with api */

          return dataProvider.update('users', {
              id: params.id,
              data:   params.data,
              previousData: params.data.previousData
          })

        }
    },
};

export default myDataProvider;
