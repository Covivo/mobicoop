import axios from 'axios';
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
        return response;
      })
  };  
}

export default new MAxios();