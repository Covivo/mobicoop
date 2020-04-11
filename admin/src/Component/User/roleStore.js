import { fetchUtils } from 'react-admin';

const initialState = [{'roles': new Array(), 'territory' : null}];
const token = localStorage.getItem('token');
const httpClient = fetchUtils.fetchJson;
const apiUrlUploadImage = process.env.REACT_APP_API+process.env.REACT_APP_SEND_IMAGES;

function reducer(state, action) {
    let intermediaire
    switch (action.type) {
        case 'delete' :
            //We delete an image : we remove her from server
            if (state[action.indice].image  !== undefined){

                const options = {}
                if (!options.headers) {
                    options.headers = new Headers({ Accept: 'application/json' });
                }
                options.headers.set('Authorization', `Bearer ${token}`);

                var lid = state[action.indice].image.id;
                httpClient(`${apiUrlUploadImage}/`+lid, {
                    method: 'DELETE',
                    headers : options.headers
                })
            }
            if (state.length===0) return state
            return state.filter( ( _ , i) => i !== action.indice)
        case 'add_pair' :
            return [...state, {'roles': new Array(), 'territory' : null}]
        case 'update' :

          if (action.nature == 'roles'){
            let retourUpdate = [...state]
            retourUpdate[action.indice]['roles'] =  action.valeur
            return retourUpdate
          }else{
            let retourUpdate = [...state]
            retourUpdate[action.indice]['territory'] =  action.valeur.name
            return retourUpdate
          }
        case 'resume_edit' :
            if (state[0]['roles'].length == 0 || state[0]['roles'] == 'none') state = [] // Empty array for edit case
            return [...state, {'roles': action.dataFormat.roles, 'territory' : action.dataFormat.territory, 'territoryName' : action.dataFormat.territoryName}]

        default :
            return state
  }
}

export {reducer, initialState}
