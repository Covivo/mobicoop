import React, { useEffect } from 'react';
import { Loading, useMutation, useTranslate } from 'react-admin';
import { Dialog, DialogTitle, makeStyles, Grid } from '@material-ui/core';
import { SolidaryMessagesThread } from './SolidaryMessagesThread';
import { solidaryLabelRenderer } from '../../../utils/renderers';
import { useSolidaryMessagesController } from './hooks/useSolidaryMessagesController';

const useStyles = makeStyles({
  loading: {
    height: '50vh',
  },
  infos: {
    padding: 20,
  },
});

export const SolidaryMessagesModal = ({ solidaryId, solidarySolutionId, onClose }) => {
  const classes = useStyles();
  const translate = useTranslate();

  const { loading, data, error, refresh } = useSolidaryMessagesController(
    solidaryId,
    solidarySolutionId
  );

  const [send, { loading: loadingSubmit }] = useMutation({}, { onSuccess: () => refresh() });

  useEffect(() => {
    if (error) onClose();
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
                  <>
                    <p>{`${translate('custom.solidary.onBehalfOf')}: `}</p>
                    <p>
                      <strong>{`${data.beneficiary.givenName} ${data.beneficiary.familyName}`}</strong>
                    </p>
                  </>
                )}
                {data.solidary && data.solidary.displayLabel && (
                  <>
                    <p>{`${translate('custom.solidary.associatedAsk')}:`}</p>
                    <p>
                      <strong>
                        {solidaryLabelRenderer({
                          record: data.solidary,
                        })}
                      </strong>
                    </p>
                  </>
                )}
              </div>
            </Grid>
          </Grid>
        </>
      ) : null}
    </Dialog>
  );
};
