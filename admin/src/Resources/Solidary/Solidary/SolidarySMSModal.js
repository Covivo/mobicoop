import React, { useState } from 'react';
import { useTranslate, useNotify, Loading } from 'react-admin';
import SendIcon from '@material-ui/icons/Send';

import {
  Dialog,
  DialogContent,
  DialogTitle,
  IconButton,
  Grid,
  makeStyles,
} from '@material-ui/core';

import { useSolidarySMSController } from './hooks/useSolidarySMSController';

const useStyles = makeStyles({
  textarea: {
    minHeight: 100,
    width: '100%',
  },
  sendButton: {
    display: 'block',
    margin: '30px auto 0 auto',
  },
  loading: {
    height: '50vh',
  },
});

export const SolidarySMSModal = ({ solidaryId, solidarySolutionId, onClose }) => {
  const translate = useTranslate();
  const classes = useStyles();
  const notify = useNotify();
  const [message, setMessage] = useState('');

  const { submit, submitting, loading, data } = useSolidarySMSController(
    solidaryId,
    solidarySolutionId
  );

  const handleSubmit = () => {
    submit(message);
    setMessage('');
    notify('SMS Envoyé avec succès', 'success');
    onClose();
  };

  const handleMessageChange = (e) => {
    setMessage(e.currentTarget.value);
  };

  return (
    <Dialog fullWidth maxWidth="md" open onClose={onClose}>
      {loading ? (
        <Loading className={classes.loading} />
      ) : (
        <>
          <DialogTitle>
            {data.beneficiary
              ? translate('custom.solidary.sendSMSTo', {
                  username: data.driver,
                })
              : translate('custom.solidary.sendSMS')}
          </DialogTitle>
          <DialogContent>
            <Grid container>
              <Grid item xs={11}>
                <textarea
                  className={classes.textarea}
                  value={message}
                  onChange={handleMessageChange}
                />
              </Grid>
              <Grid item xs={1}>
                <IconButton
                  onClick={handleSubmit}
                  disabled={submitting || message.trim() === ''}
                  className={classes.sendButton}
                >
                  <SendIcon />
                </IconButton>
              </Grid>
            </Grid>
          </DialogContent>
        </>
      )}
    </Dialog>
  );
};
