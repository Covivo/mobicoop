import React from 'react';
import isAuthorized from '../../Auth/permissions'
import { Button,useTranslate } from 'react-admin';
import Close from '@material-ui/icons/Close';

import {useUnselectAll} from 'react-admin';


const ResetButton = (props) => {

  const unselectAll = useUnselectAll();
  const translate = useTranslate();

  const handleReset = () => {
    unselectAll(props.resource);
  }

  return (  <Button
            label={translate('custom.label.campaign.statusCampaign.init')}
            color="secondary"
            onClick={handleReset}
        >
            <Close />
        </Button>
      )

};

export default ResetButton;
