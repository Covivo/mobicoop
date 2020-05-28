import React from 'react';
import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogContentText,
  DialogTitle,
  Button,
} from '@material-ui/core';
import { useTranslate } from 'react-admin';

const RgpdConsent = ({ isOpen, onClose, iAgree }) => {
  // eslint-disable-next-line no-unused-vars
  const translate = useTranslate();
  const instance = process.env.REACT_APP_INSTANCE_NAME;

  const handleClose = () => {
    onClose();
  };
  const handleIagree = () => {
    onClose();
    iAgree(true);
  };

  return (
    <Dialog
      open={isOpen}
      onClose={handleClose}
      aria-labelledby="alert-dialog-title"
      aria-describedby="alert-dialog-description"
    >
      <DialogTitle id="alert-dialog-title">{translate('custom.rgpd.modal.titre')}</DialogTitle>
      <DialogContent>
        <DialogContentText id="alert-dialog-description">
          {translate('custom.rgpd.modal.texte', { instanceName: instance })}
        </DialogContentText>
      </DialogContent>
      <DialogActions>
        <Button onClick={handleClose} color="primary">
          {translate('custom.rgpd.modal.buttonDisagree')}
        </Button>
        <Button onClick={handleIagree} color="primary" autoFocus variant="contained">
          {translate('custom.rgpd.modal.buttonAgree')}
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default RgpdConsent;
