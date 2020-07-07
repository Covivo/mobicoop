import { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';

export const useSolidary = (solidaryId) => {
  const dataProvider = useDataProvider();
  const [solidary, setSolidary] = useState(null);
  const [loading, setLoading] = useState(false);

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
  }, [solidaryId]);

  return { solidary, loading };
};
