import axios from 'axios';
import { store } from '../store';

class MAxios {
  get(route,params) {
    return axios
      .get(route, params,{
        headers:{
          'content-type': 'application/json',
          'X-LOCALE': localStorage.getItem('X-LOCALE')
        }
      })
      .then( response => {
        this.updateStore(response);
        return response;
      })
  };
  post(route,params) {
    return axios
      .post(route, params,{
        headers:{
          'content-type': 'application/json',
          'X-LOCALE': localStorage.getItem('X-LOCALE')
        }
      })
      .then( response => {
        this.updateStore(response);
        return response;
      })
  };
  put(route,params) {
    return axios
      .put(route, params,{
        headers:{
          'content-type': 'application/json',
          'X-LOCALE': localStorage.getItem('X-LOCALE')
        }
      })
      .then( response => {
        this.updateStore(response);
        return response;
      })
  };
  delete(route,params) {
    return axios
      .delete(route, params,{
        headers:{
          'content-type': 'application/json',
          'X-LOCALE': localStorage.getItem('X-LOCALE')
        }
      })
      .then( response => {
        this.updateStore(response);
        return response;
      })
  };
  updateStore(response){
    if(response.data.gamificationNotifications){
      if(localStorage.getItem('gamificationInLocalStorage')=='1'){
        localStorage.setItem('gamificationNotifications',JSON.stringify(response.data.gamificationNotifications));
        localStorage.removeItem('gamificationInLocalStorage');
      }
      else{
        store.commit('gn/updateGamificationNotifications',response.data.gamificationNotifications);
      }
    }
  }
}

export default new MAxios();