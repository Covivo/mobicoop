import React , {Fragment, useState } from 'react';

import {
  SelectInput,useDataProvider,useTranslate
} from 'react-admin';
import SaveOutlinedIcon from '@material-ui/icons/SaveOutlined';

import { statusChoices } from '../Community/communityChoices'
import Snackbar from '@material-ui/core/Snackbar';
import IconButton from '@material-ui/core/IconButton';
import CloseIcon from '@material-ui/icons/Close';

import Select from '@material-ui/core/Select';
import MenuItem from '@material-ui/core/MenuItem';

import { makeStyles } from '@material-ui/core/styles'

const useStyles = makeStyles({
    icon: {
        display: 'inline-block',
        verticalAlign: 'middle',
        cursor : 'pointer',
        marginTop:'0.3rem',
      },
      select : {

        marginRight: '1rem',
      }
})

const SelectNewStatus = (props) => {

  const [show,setShow] = useState(false);
  const [showSnack,setShowSnack] = useState(false);
  const [newStatut, setNewStatus] = useState(props.record.status);
  const dataProvider = useDataProvider();
  const translate = useTranslate();
  const classes = useStyles()

  const handleChangeButton = (e) => {
    setNewStatus(e.target.value)
    setShow(true);
  }

  const handleClickSave = () => {
    setShow(false);
    dataProvider.update('community_users', {
        id: 'community_users/'+props.record.originId,
        data: { status: newStatut },
    });
    setShowSnack(true);
  }

  const handleClose = () => {
    setShowSnack(false);
  };

    return (
      <Fragment>
          <Select
               value={newStatut}
               onChange={(e) => handleChangeButton(e) }
               className={classes.select}
             >
              { statusChoices.map( d =>  <MenuItem value={d.id}>{d.name}</MenuItem> ) }
             </Select>
          { show && <SaveOutlinedIcon onClick={() => handleClickSave() }  className={classes.icon} /> }

          <Snackbar
            anchorOrigin={{
              vertical: 'bottom',
              horizontal: 'center',
            }}
            open={showSnack}
            onClose={handleClose}
            message={translate('custom.alert.valueSaved')}
            autoHideDuration={2000}
            action={
              <React.Fragment>
                <IconButton
                  aria-label="close"
                  color="inherit"
                  onClick={handleClose}
                >
                  <CloseIcon />
                </IconButton>
              </React.Fragment>
            }
          />
      </Fragment>
  )
};

export default SelectNewStatus
