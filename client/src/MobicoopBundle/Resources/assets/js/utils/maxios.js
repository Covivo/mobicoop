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
        if(response.data.gamificationNotifications){
          this.updateStore(response.data.gamificationNotifications);
        }
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
        if(response.data.gamificationNotifications){
          this.updateStore(response.data.gamificationNotifications);
        }
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
        if(response.data.gamificationNotifications){
          this.updateStore(response.data.gamificationNotifications);
        }
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
        if(response.data.gamificationNotifications){
          this.updateStore(response.data.gamificationNotifications);
        }
        return response;
      })
  };
  updateStore(gamificationNotifications){
    store.commit('gn/updateGamificationNotifications',gamificationNotifications);
  }
}

export default new MAxios();