import React from 'react';
import PropTypes from 'prop-types';
import { Grid } from '@material-ui/core';

import DayChip from './DayChip';
import { utcDateFormat } from '../../../../utils/date';

export const formatDate = (d) => utcDateFormat(d, "dd'/'MM'/'yyyy");
const formatDateTime = (d) => utcDateFormat(d);
const formatHour = (d) => utcDateFormat(d, "HH'h'mm");

const SolidarySchedule = ({
  frequency,
  outwardDatetime,
  outwardDeadlineDatetime,
  returnDatetime,
  returnDeadlineDatetime,
  deadlineDate,
  monCheck,
  tueCheck,
  wedCheck,
  thuCheck,
  friCheck,
  satCheck,
  sunCheck,
  marginDuration,
}) => {
  const formatOutwardDatetime = (date) => {
    return marginDuration > 0 ? utcDateFormat(date, "dd'/'MM'/'yyyy") : '';
  };

  const getMarginDate = () => {
    const hour = formatHour(outwardDatetime);
    if (marginDuration > 0) {
      if (hour === '10h30') return `${formatOutwardDatetime(outwardDatetime)} 8h-13h`;
      if (hour === '14h30') return `${formatOutwardDatetime(outwardDatetime)} 13h-18h`;
      if (hour === '18h30') return `${formatOutwardDatetime(outwardDatetime)} 18h-21h`;
    }
    return `${formatDateTime(outwardDatetime)}`;
  };

  if (frequency === 2) {
    // Regular
    return (
      <Grid container direction="column" spacing={2}>
        <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
          {outwardDatetime && (
            <Grid item>
              <b>Début:</b>
              <br />
              {formatDate(outwardDatetime)}
            </Grid>
          )}
          {returnDatetime && (
            <Grid item>
              <b>Fin:</b>
              <br />
              {deadlineDate ? formatDate(deadlineDate) : formatDate(returnDatetime)}
            </Grid>
          )}
        </Grid>
        <Grid item>
          {[
            { label: 'L', condition: monCheck },
            { label: 'M', condition: tueCheck },
            { label: 'Me', condition: wedCheck },
            { label: 'J', condition: thuCheck },
            { label: 'V', condition: friCheck },
            { label: 'S', condition: satCheck },
            { label: 'D', condition: sunCheck },
          ].map(({ label, condition }) => (
            <DayChip key={label} label={label} condition={condition} />
          ))}
        </Grid>
        {outwardDatetime && returnDatetime && (
          <Grid item>
            <Grid container direction="row" justify="space-between">
              <Grid item>
                <b>Aller:</b> {formatHour(outwardDatetime)}
              </Grid>
              <Grid item>
                <b>Retour:</b> {formatHour(returnDatetime)}
              </Grid>
            </Grid>
          </Grid>
        )}
      </Grid>
    );
  }

  return (
    <Grid container spacing={1}>
      <Grid item xs={4}>
        Aller :
      </Grid>
      <Grid item xs={8}>
        {outwardDeadlineDatetime
          ? `entre le ${formatDateTime(outwardDatetime)} et le ${formatDate(
              outwardDeadlineDatetime
            )}`
          : getMarginDate(outwardDatetime)}
      </Grid>
      {returnDatetime && (
        <>
          <Grid item xs={4}>
            Retour :
          </Grid>
          <Grid item xs={8}>
            {returnDeadlineDatetime
              ? `entre le ${formatDateTime(returnDatetime)} et le ${formatDate(
                  returnDeadlineDatetime
                )}`
              : formatDateTime(returnDatetime)}
          </Grid>
        </>
      )}
      {/* marginDuration && (
        <>
          <Grid item xs={4}>
            Marge :
          </Grid>
          <Grid item xs={8}>
            {Math.round(marginDuration / 3600)} heures
          </Grid>
        </>
      ) */}
    </Grid>
  );
};

SolidarySchedule.propTypes = {
  frequency: PropTypes.number.isRequired,
  marginDuration: PropTypes.number.isRequired,
  outwardDatetime: PropTypes.string.isRequired,
  outwardDeadlineDatetime: PropTypes.string,
  returnDatetime: PropTypes.string,
  returnDeadlineDatetime: PropTypes.string,
  monCheck: PropTypes.bool,
  tueCheck: PropTypes.bool,
  wedCheck: PropTypes.bool,
  thuCheck: PropTypes.bool,
  friCheck: PropTypes.bool,
  satCheck: PropTypes.bool,
  sunCheck: PropTypes.bool,
};

SolidarySchedule.defaultProps = {
  monCheck: false,
  tueCheck: false,
  wedCheck: false,
  thuCheck: false,
  friCheck: false,
  satCheck: false,
  sunCheck: false,
  outwardDeadlineDatetime: null,
  returnDatetime: null,
  returnDeadlineDatetime: null,
};

export default SolidarySchedule;
