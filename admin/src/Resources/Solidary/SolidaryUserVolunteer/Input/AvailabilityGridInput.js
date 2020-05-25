import React from 'react';
import { BooleanInput, useTranslate } from 'react-admin';

import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  makeStyles,
} from '@material-ui/core';

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
    maxWidth: 550,
    '& .MuiFormHelperText-root': {
      display: 'none!important',
    },
    '& .MuiFormControlLabel-root': {
      justifyContent: 'center',
    },
  },
});

export const AvailabilityGridInput = () => {
  const translate = useTranslate();
  const rows = buildVoluntaryAvailabilityRows();
  const classes = useStyles();

  return (
    <TableContainer className={classes.table} component={Paper}>
      <Table aria-label="simple table">
        <TableHead>
          <TableRow>
            <TableCell align="center">&nbsp;</TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.morning`)}
              {` (6h-14h)`}
            </TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.afternoon`)}
              {` (12h-19h)`}
            </TableCell>
            <TableCell align="center">
              {translate(`custom.solidary_volunteers.edit.evening`)}
              {` (17h-23h)`}
            </TableCell>
          </TableRow>
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
