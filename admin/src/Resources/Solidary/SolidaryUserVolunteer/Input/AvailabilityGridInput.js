import React from 'react';
import { BooleanInput, useTranslate, FormDataConsumer } from 'react-admin';
import PropTypes from 'prop-types';
import EditIcon from '@material-ui/icons/Edit';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  makeStyles,
} from '@material-ui/core';

import { resolveVoluntaryAvailabilityHourRanges } from '../utils/resolveVoluntaryAvailabilityHourRanges';
import { AvailabilityRangeDialogButton } from './AvailabilityRangeDialogButton';

const createAvailabilityRow = (day) => ({
  day,
  morning: `m${day}`,
  afternoon: `a${day}`,
  evening: `e${day}`,
});

const buildVoluntaryAvailabilityRows = () => {
  return [
    createAvailabilityRow('Mon'),
    createAvailabilityRow('Tue'),
    createAvailabilityRow('Wed'),
    createAvailabilityRow('Thu'),
    createAvailabilityRow('Fri'),
    createAvailabilityRow('Sat'),
    createAvailabilityRow('Sun'),
  ];
};

const useStyles = makeStyles({
  table: {
    maxWidth: 800,
    '& .MuiFormHelperText-root': {
      display: 'none!important',
    },
    '& .MuiFormControlLabel-root': {
      justifyContent: 'center',
    },
  },
});

export const AvailabilityGridInput = (props) => {
  const translate = useTranslate();
  const rows = buildVoluntaryAvailabilityRows();
  const classes = useStyles();

  return (
    <TableContainer className={classes.table}>
      <Table aria-label="simple table">
        <TableHead>
          <FormDataConsumer>
            {({ formData }) => {
              const hourRanges = resolveVoluntaryAvailabilityHourRanges(formData);

              return (
                <TableRow>
                  <TableCell align="center">&nbsp;</TableCell>
                  <TableCell align="center">
                    {translate(`custom.solidary_volunteers.edit.morning`)}
                    {` (${hourRanges.morning || `6h-14h`})`}
                    <AvailabilityRangeDialogButton label={<EditIcon />} source="m" {...props} />
                  </TableCell>
                  <TableCell align="center">
                    {translate(`custom.solidary_volunteers.edit.afternoon`)}
                    {` (${hourRanges.afternoon || `12h-19h`})`}
                    <AvailabilityRangeDialogButton label={<EditIcon />} source="a" {...props} />
                  </TableCell>
                  <TableCell align="center">
                    {translate(`custom.solidary_volunteers.edit.evening`)}
                    {` (${hourRanges.evening || `17h-23h`})`}
                    <AvailabilityRangeDialogButton label={<EditIcon />} source="e" {...props} />
                  </TableCell>
                </TableRow>
              );
            }}
          </FormDataConsumer>
        </TableHead>
        <TableBody>
          {rows.map((row) => (
            <TableRow key={row.name}>
              <TableCell component="th" scope="row">
                {translate(`resources.solidary_volunteers.fields.${row.day}`)}
              </TableCell>
              <TableCell align="center">
                <BooleanInput label={null} source={row.morning} />
              </TableCell>
              <TableCell align="center">
                <BooleanInput label={null} source={row.afternoon} />
              </TableCell>
              <TableCell align="center">
                <BooleanInput label={null} source={row.evening} />
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

AvailabilityGridInput.propTypes = {
  record: PropTypes.object.isRequired,
};
