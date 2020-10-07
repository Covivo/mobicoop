const kibanaLogout = () => {
  const kibanaLogoutApi = `${process.env.REACT_APP_KIBANA_URL}/logout`;
  fetch(kibanaLogoutApi, {
    credentials: 'include',
    method: 'GET',
  }).then((reponse) => {
    console.log('logged out kibana. Status :', reponse.statusText);
  });
};

export default kibanaLogout;
