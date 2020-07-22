import { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';

export const useSolidary = (solidaryId) => {
  const dataProvider = useDataProvider();
  const [solidary, setSolidary] = useState(null);

  useEffect(() => {
    if (solidaryId) {
      dataProvider.getOne('solidary', { id: solidaryId }).then((response) => {
        if (response) setSolidary(response.data);
      });
    } else {
      setSolidary(null);
    }
  }, [solidaryId]);

  return solidary;
};
