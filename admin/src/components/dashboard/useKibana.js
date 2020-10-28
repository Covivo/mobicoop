import { useEffect, useState } from 'react';
import { useTranslate } from 'react-admin';

const useKibana = () => {
  const [status, setStatus] = useState('DISCONNECTED'); // Etat de la connexion à Kibana
  const [error, setError] = useState(''); // Etat de la connexion à Kibana
  const translate = useTranslate();
  const instanceName = process.env.REACT_APP_SCOPE_INSTANCE_NAME;
  const kibanaAuthenticationApi = `${process.env.REACT_APP_KIBANA_URL}/login/${instanceName}`;
  const kibanaCheckCookieApi = `${process.env.REACT_APP_KIBANA_URL}/check`;

  useEffect(() => {
    const getKibanaCookie = async () => {
      fetch(kibanaAuthenticationApi, {
        credentials: 'include',
        headers: new global.Headers({
          Authorization: `Bearer ${global.localStorage.getItem('token')}`,
        }),
        method: 'GET',
      })
        .then((reponse) => {
          // Should check if cookie is there
          if (reponse.status === 200) {
            setStatus('INITIALIZED');
          } else {
            setStatus('DISCONNECTED');
            setError(translate('custom.dashboard.kibanaAuthenticationApiReturnSomethingWrong'));
          }
        })
        .catch((e) => {
          // eslint-disable-next-line no-console
          console.log('Ereur lors de la connexion à Kibana :', e);
          setStatus('DISCONNECTED');
          setError(translate('custom.dashboard.kibanaAuthenticationApiFetchError'));
        });
    };
    if (global.localStorage.getItem('token') && instanceName && kibanaAuthenticationApi) {
      getKibanaCookie();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    const kibanaCheckApi = async () => {
      fetch(kibanaCheckCookieApi, {
        method: 'GET',
        credentials: 'include',
      })
        .then((response) => response.text())
        .then((body) => {
          if (body === 'Authorized') {
            setStatus('CONNECTED');
          }
        });
    };
    const interval = setInterval(async () => {
      status !== 'CONNECTED' && (await kibanaCheckApi());
    }, 3000);
    return () => clearInterval(interval);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [status]);

  return [status, error];
};

export { useKibana };
