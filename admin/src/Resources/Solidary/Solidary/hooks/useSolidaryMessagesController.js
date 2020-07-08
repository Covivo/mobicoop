import { useState, useEffect } from 'react';
import { useDataProvider } from 'react-admin';

export const useSolidaryMessagesController = (solidaryId, solidarySolutionId) => {
  const dataProvider = useDataProvider();

  const [beneficiary, setBeneficiary] = useState(null);
  const [solidary, setSolidary] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchMessages = () => {
    setLoading(true);
    dataProvider
      .getOne('solidaries', { id: `/solidaries/${solidaryId}` })
      .then(async ({ data: solidaryResult }) => {
        setSolidary(solidaryResult);
        if (solidaryResult.solidaryUser && solidaryResult.solidaryUser.user) {
          setBeneficiary(solidaryResult.solidaryUser.user);
        }
      })
      .catch((e) => {
        setError(e);
        setLoading(false);
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchMessages();
  }, [solidaryId, solidarySolutionId]); // eslint-disable-line react-hooks/exhaustive-deps

  const ask =
    solidary && solidary.asksList.find((i) => i.solidarySolutionId === solidarySolutionId);

  return {
    data: {
      messages: ask ? ask.messages : [],
      driver: ask ? ask.driver : null,
      solidary,
      beneficiary,
    },
    loading,
    error,
    refresh: fetchMessages,
  };
};
