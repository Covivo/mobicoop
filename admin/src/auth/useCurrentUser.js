import { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';

export const useCurrentUser = () => {
  const dataProvider = useDataProvider();
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const userId = localStorage.getItem('id');

  useEffect(() => {
    setLoading(true);

    userId &&
      dataProvider
        .getOne('users', { id: `/users/${userId}` })
        .then(({ data }) => {
          setUser(data);
          setLoading(false);
        })
        .finally(() => setLoading(false));
  }, [userId]);

  return { user, loading };
};
