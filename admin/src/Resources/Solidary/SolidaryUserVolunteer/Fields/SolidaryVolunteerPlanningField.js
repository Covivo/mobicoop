import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { fetchStart, fetchEnd, useTranslate } from 'react-admin';
import { format, startOfToday, addDays } from 'date-fns';
import { fr } from 'date-fns/locale';
import MenuItem from '@material-ui/core/MenuItem';
import PopupState, { bindTrigger, bindMenu } from 'material-ui-popup-state';
import ArrowDropDownIcon from '@material-ui/icons/ArrowDropDown';
import { stringify } from 'qs';
import PropTypes from 'prop-types';
import { useHistory } from 'react-router-dom';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  makeStyles,
  Menu,
  Grid,
} from '@material-ui/core';

import { fetchHydra } from '../../../../dataProvider';
import { resolveVoluntaryAvailabilityHourRanges } from '../utils/resolveVoluntaryAvailabilityHourRanges';

const entrypoint = process.env.REACT_APP_API;

const useStyles = makeStyles((theme) => ({
  table: {
    maxHeight: '400px',
    '& .MuiTableCell-stickyHeader': {
      backgroundColor: theme.palette.background.paper,
    },
    '& .MuiFormHelperText-root': {
      display: 'none!important',
    },
    '& .MuiFormControlLabel-root': {
      justifyContent: 'center',
    },
  },
  availabilitySlotZone: {
    height: '30px',
    minWidth: '200px',
    textAlign: 'left',
    padding: '5px 7px',
  },
}));

const AvaibilitySlot = ({ slot }) => {
  const classes = useStyles();
  const router = useHistory();

  if (!slot) {
    return <div className={classes.availabilitySlotZone} style={{ background: '#aaa' }} />;
  }

  const handleOpenSolidary = (popupState) => () => {
    popupState.close();
    router.push(`/solidaries/${slot.solidaryId}/show`);
  };

  const handleOpenSolidaryDiscuss = (popupState) => () => {
    popupState.close();
    router.push(`/solidaries/${slot.solidaryId}/show`);
  };

  const handleOpenSolidarySolicitation = (popupState) => () => {
    popupState.close();
    router.push(`/solidaries/${slot.solidaryId}/show`);
  };

  return (
    <PopupState variant="popover">
      {(popupState) => (
        <>
          <div
            className={classes.availabilitySlotZone}
            style={{ background: '#ddd' }}
            {...bindTrigger(popupState)}
          >
            <Grid container justify="space-between" alignItems="center">
              <Grid item>{`${slot.solidaryId} - ${slot.beneficiary}`}</Grid>
              <Grid item>
                <ArrowDropDownIcon />
              </Grid>
            </Grid>
          </div>
          <Menu {...bindMenu(popupState)}>
            <MenuItem onClick={handleOpenSolidary(popupState)}>
              <span>{`Voir demande solidaire #${slot.solidaryId}`}</span>
            </MenuItem>
            <MenuItem onClick={handleOpenSolidaryDiscuss(popupState)}>
              <span>{`Contacter ${slot.beneficiary} par messagerie pour la demande #${slot.solidaryId}`}</span>
            </MenuItem>
            <MenuItem onClick={handleOpenSolidarySolicitation(popupState)}>
              <span>{`Soliciter réponse de ${slot.beneficiary} sur demande #${slot.solidaryId}`}</span>
            </MenuItem>
          </Menu>
        </>
      )}
    </PopupState>
  );
};

AvaibilitySlot.defaultProps = { slot: null };

AvaibilitySlot.propTypes = {
  slot: PropTypes.shape({
    solidaryId: PropTypes.string,
    beneficiary: PropTypes.string,
  }),
};

export const SolidaryVolunteerPlanningField = ({ record }) => {
  const dispatch = useDispatch();
  const translate = useTranslate();
  const classes = useStyles();
  const [plannings, setPlannings] = useState(null);

  const hourRanges = resolveVoluntaryAvailabilityHourRanges(record);

  useEffect(() => {
    dispatch(fetchStart());

    fetchHydra(
      `${entrypoint}/solidary_volunteer_plannings?${stringify({
        startDate: format(startOfToday(), 'Y-M-d'),
        endDate: format(addDays(startOfToday(), 30), 'Y-M-d'),
        solidaryVolunteerId: record.id,
      })}`
    )
      .then(({ json }) => {
        setPlannings(json['hydra:member']);
      })
      .finally(() => {
        dispatch(fetchEnd());
      });
  }, [record]); // eslint-disable-line

  if (!plannings) {
    return null;
  }

  return (
    <TableContainer className={classes.table}>
      <Table stickyHeader aria-label="simple table">
        <TableHead>
          <TableRow>
            <TableCell align="center">&nbsp;</TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.morning`)}
              {` (${hourRanges.morning || `6h-14h`})`}
            </TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.afternoon`)}
              {` (${hourRanges.afternoon || `12h-19h`})`}
            </TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.evening`)}
              {` (${hourRanges.evening || `17h-23h`})`}
            </TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {plannings.map((row) => (
            <TableRow key={row.name}>
              <TableCell component="th" scope="row">
                {format(new Date(row.date), 'eee dd LLL', {
                  locale: fr,
                })}
              </TableCell>
              <TableCell align="center">
                <AvaibilitySlot slot={row.morningSlot} />
              </TableCell>
              <TableCell align="center">
                <AvaibilitySlot slot={row.afternoonSlot} />
              </TableCell>
              <TableCell align="center">
                <AvaibilitySlot slot={row.eveningSlot} />
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

SolidaryVolunteerPlanningField.propTypes = {
  record: PropTypes.object.isRequired,
};
