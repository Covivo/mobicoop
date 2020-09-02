import { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';

export const useSolidary = (solidaryId) => {
  const dataProvider = useDataProvider();
  const [refreshToken, setRefreshToken] = useState(0);
  const [solidary, setSolidary] = useState(null);
  const [loading, setLoading] = useState(false);

  const refresh = () => setRefreshToken((token) => token + 1);

  useEffect(() => {
    if (solidaryId) {
      setLoading(true);
      dataProvider.getOne('solidaries', { id: solidaryId }).then((response) => {
        if (response) {
          setSolidary(response.data);
          setLoading(false);
        }
      });
    } else {
      setSolidary(null);
    }
  }, [refreshToken, solidaryId]);

  return { solidary, refresh, loading };
};
