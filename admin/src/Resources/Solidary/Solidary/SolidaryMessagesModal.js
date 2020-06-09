import React, { useState, useEffect } from 'react';
import { Loading, useDataProvider, useMutation, useTranslate } from 'react-admin';
import { Dialog, DialogTitle, makeStyles, Grid } from '@material-ui/core';
import { SolidaryMessagesThread } from './SolidaryMessagesThread';

const useStyles = makeStyles({
  loading: {
    height: '50vh',
  },
  infos: {
    padding: 20,
  },
});

const useSolidaryMessagesThread = (solidaryId, solidarySolutionId) => {
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

        dataProvider
          .getOne('solidary_users', { id: solidaryResult.solidaryUser })
          .then(({ data }) => dataProvider.getOne('users', { id: data.user }))
          .then(({ data }) => setBeneficiary(data))
          .catch(() => {
            /* Silently fail */
          });
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

export const SolidaryMessagesModal = ({ solidaryId, solidarySolutionId, onClose }) => {
  const classes = useStyles();
  const translate = useTranslate();

  const { loading, data, error, refresh } = useSolidaryMessagesThread(
    solidaryId,
    solidarySolutionId
  );

  const [send, { loading: loadingSubmit }] = useMutation({}, { onSuccess: () => refresh() });

  useEffect(() => {
    if (error) {
      onClose();
    }
  }, [error]);

  const handleSubmit = (content) => {
    send({
      type: 'create',
      resource: 'solidary_contacts',
      payload: {
        data: {
          solidarySolution: `/solidary_solutions/${solidarySolutionId}`,
          media: ['/media/1'], // "/media/1" is the media type for tchat
          content,
        },
      },
    });
  };

  return (
    <Dialog fullWidth maxWidth="md" open onClose={onClose}>
      {loading && data.messages.length === 0 ? (
        <Loading className={classes.loading} />
      ) : data ? (
        <>
          <DialogTitle>
            {data.driver
              ? translate('custom.solidary.internalMessagesWith', { username: data.driver })
              : translate('custom.solidary.internalMessages')}
          </DialogTitle>
          <Grid container>
            <Grid item xs={8}>
              <SolidaryMessagesThread
                messages={data.messages.map((message) => ({
                  ...message,
                  owner: data.beneficiary && data.beneficiary.originId === message.userId,
                }))}
                onSubmit={handleSubmit}
                submitting={loadingSubmit}
              />
            </Grid>
            <Grid item xs={4}>
              <div className={classes.infos}>
                {data.beneficiary && (
                  <p>
                    {`${translate('custom.solidary.onBehalfOf')}: `}
                    <b>{`${data.beneficiary.givenName} ${data.beneficiary.familyName}`}</b>
                  </p>
                )}
                {data.solidary && data.solidary.displayLabel && (
                  <p>
                    {`${translate('custom.solidary.associatedAsk')}: ${data.solidary.displayLabel}`}
                  </p>
                )}
              </div>
            </Grid>
          </Grid>
        </>
      ) : null}
    </Dialog>
  );
};
