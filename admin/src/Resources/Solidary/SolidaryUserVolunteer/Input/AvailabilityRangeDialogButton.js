import React from 'react';
import { Button, Dialog, DialogActions, DialogContent } from '@material-ui/core';
import PropTypes from 'prop-types';

import { AvailabilityRangeInput } from './AvailabilityRangeInput';

export const AvailabilityRangeDialogButton = ({ label, ...props }) => {
  const [open, setOpen] = React.useState(false);

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  return (
    <>
      <Dialog open={open} onClose={handleClose} aria-labelledby="form-dialog-title">
        <DialogContent>
          <AvailabilityRangeInput {...props} />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose} color="primary">
            OK
          </Button>
        </DialogActions>
      </Dialog>
      <Button onClick={handleOpen}>{label}</Button>
    </>
  );
};

AvailabilityRangeDialogButton.propTypes = {
  label: PropTypes.element,
};

AvailabilityRangeDialogButton.defaultProps = {
  label: 'Edit',
};
