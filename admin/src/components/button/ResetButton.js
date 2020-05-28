import React from 'react';
import { Button, useTranslate, useUnselectAll } from 'react-admin';
import Close from '@material-ui/icons/Close';

const ResetButton = (props) => {
  const unselectAll = useUnselectAll();
  const translate = useTranslate();

  const handleReset = () => {
    unselectAll(props.resource);
  };

  return (
    <Button label={translate('custom.alert.clearSelected')} color="secondary" onClick={handleReset}>
      <Close />
    </Button>
  );
};

export default ResetButton;
