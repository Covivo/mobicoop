import { fetchUtils } from 'react-admin';

const initialState = [];
const token = localStorage.getItem('token');
const httpClient = fetchUtils.fetchJson;
const apiUrlUploadImage = process.env.REACT_APP_API+process.env.REACT_APP_SEND_IMAGES;

function reducer(state, action) {
    let intermediaire
    switch (action.type) {
        case 'up':
            if (action.indice===0 || state.length===0) return state
            let retourUp = [...state]
            intermediaire   = retourUp[action.indice]
            retourUp[action.indice] = retourUp[ action.indice - 1] 
            retourUp[ action.indice - 1] = intermediaire
            return retourUp
        case 'down':
            if (state.length===0 || action.indice > (state.length-1)) return state
            let retourDown = [...state]
            intermediaire   = retourDown[action.indice]
            retourDown[action.indice] = retourDown[ action.indice + 1] 
            retourDown[ action.indice + 1] = intermediaire
            return retourDown
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
        case 'add_title' :
            return [...state, {titre:"Nouveau titre"}]
        case 'add_text' :
            return [...state, {texte:"<p>Nouveau texte</p>"}]
        case 'add_image' :
            return [...state, {image:""}]
        case 'update' :
            let retourUpdate = [...state]
            console.log("action.valeur : ", action.valeur)
            retourUpdate[action.indice] = { [action.nature] : action.valeur }
            return retourUpdate
        default :
            return state
  }
}

export {reducer, initialState}



