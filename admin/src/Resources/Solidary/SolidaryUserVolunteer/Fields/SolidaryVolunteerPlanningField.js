import React, { useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import { fetchStart, fetchEnd, useTranslate } from 'react-admin';
import { format, startOfToday, addDays, getISOWeek } from 'date-fns';
import { fr } from 'date-fns/locale';
import { MenuItem, Typography } from '@material-ui/core';
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

import { resolveVoluntaryAvailabilityHourRanges } from '../utils/resolveVoluntaryAvailabilityHourRanges';
import { fetchJson } from '../../../../fetchJson';

import {
  solidaryAskStatusColors,
  solidaryAskStatusIcons,
  solidaryAskStatusLabels,
} from '../../../../constants/solidaryAskStatus';

const entrypoint = process.env.REACT_APP_API;

const useStyles = makeStyles((theme) => ({
  table: {
    maxHeight: '600px',
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

const AvaibilitySlot = ({ slot, onOpenMessaging }) => {
  const classes = useStyles();
  const router = useHistory();
  const translate = useTranslate();

  if (!slot) {
    return <div className={classes.availabilitySlotZone} style={{ background: '#aaa' }} />;
  }

  const handleOpenSolidary = (popupState) => () => {
    popupState.close();
    const raSolidaryId = encodeURIComponent(`/solidaries/${slot.solidaryId}`);
    router.push(`/solidaries/${raSolidaryId}/show`);
  };

  const handleOpenSolidaryDiscuss = (popupState) => () => {
    popupState.close();
    onOpenMessaging(slot);
  };

  // @TODO: Open the right popup from the MenuItem below
  // const handleOpenSolidarySolicitation = (popupState) => () => {
  //   popupState.close();
  //   const raSolidaryId = encodeURIComponent(`/solidaries/${slot.solidaryId}`);
  //   router.push(`/solidaries/${raSolidaryId}/show`);
  // };

  const StatusIcon = solidaryAskStatusIcons[slot.status];

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
              <Grid item>
                <span
                  title={translate(solidaryAskStatusLabels[slot.status])}
                  style={{ color: solidaryAskStatusColors[slot.status] }}
                >
                  <StatusIcon style={{ verticalAlign: 'middle' }} />
                  <span>{` ${slot.solidaryId} - ${slot.beneficiary}`}</span>
                </span>
              </Grid>
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
            {/* <MenuItem onClick={handleOpenSolidarySolicitation(popupState)}>
              <span>{`Solliciter réponse de ${slot.beneficiary} sur demande #${slot.solidaryId}`}</span>
            </MenuItem> */}
          </Menu>
        </>
      )}
    </PopupState>
  );
};

AvaibilitySlot.defaultProps = { slot: null, onOpenMessaging: () => {} };

AvaibilitySlot.propTypes = {
  onOpenMessaging: PropTypes.func,
  slot: PropTypes.shape({
    solidaryId: PropTypes.number,
    beneficiary: PropTypes.string,
  }),
};

const groupByDateWeek = (objects) =>
  objects.reduce((agg, obj) => {
    const week = getISOWeek(new Date(obj.date));
    if (!agg[week]) {
      agg[week] = [];
    }

    agg[week].push(obj);
    return agg;
  }, {});

export const SolidaryVolunteerPlanningField = ({ record, onOpenMessaging }) => {
  const dispatch = useDispatch();
  const translate = useTranslate();
  const classes = useStyles();
  const [plannings, setPlannings] = useState(null);

  const hourRanges = resolveVoluntaryAvailabilityHourRanges(record);

  useEffect(() => {
    dispatch(fetchStart());

    fetchJson(
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

  const planningWeeks = groupByDateWeek(plannings);

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
          {Object.keys(planningWeeks).map((week) => {
            const plannings = planningWeeks[week];

            const rows = plannings.map((row) => (
              <TableRow key={row.date}>
                <TableCell component="th" scope="row">
                  {format(new Date(row.date), 'eee dd LLL', {
                    locale: fr,
                  })}
                </TableCell>
                <TableCell align="center">
                  <AvaibilitySlot
                    slot={
                      // Use lines bellow for tests
                      // {
                      //   status: 0,
                      //   solidaryId: 18,
                      //   solidarySolutionId: 6,
                      //   beneficiary: `${record.givenName} ${record.familyName}`,
                      // } ||
                      row.morningSlot
                    }
                    onOpenMessaging={onOpenMessaging}
                  />
                </TableCell>
                <TableCell align="center">
                  <AvaibilitySlot slot={row.afternoonSlot} onOpenMessaging={onOpenMessaging} />
                </TableCell>
                <TableCell align="center">
                  <AvaibilitySlot slot={row.eveningSlot} onOpenMessaging={onOpenMessaging} />
                </TableCell>
              </TableRow>
            ));

            return [
              <TableRow key={week}>
                <TableCell colSpan={3} component="th" scope="row">
                  <Typography variant="h5">{`${translate('custom.week')} ${week}`}</Typography>
                </TableCell>
              </TableRow>,
              ...rows,
            ];
          })}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

SolidaryVolunteerPlanningField.propTypes = {
  record: PropTypes.object,
  onOpenMessaging: PropTypes.func,
};

SolidaryVolunteerPlanningField.defaultProps = {
  record: {},
  onOpenMessaging: () => {},
};
