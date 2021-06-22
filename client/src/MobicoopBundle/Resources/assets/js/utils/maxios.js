import axios from 'axios';
class MAxios {
  get(route,params) {
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
}

export default new MAxios();