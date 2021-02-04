import React from 'react';
import { useField } from 'react-final-form';
import { useTranslate } from 'react-admin';

import SolidaryQuestion from './SolidaryQuestion';
import { utcDateFormat } from '../../../../utils/date';

export const SolidaryPunctualAskSummary = ({ regularMode = false }) => {
  const translate = useTranslate();

  const {
    input: { value: outwardDatetime },
  } = useField('outwardDatetime');

  const {
    input: { value: days },
  } = useField('days');

  const {
    input: { value: outwardDeadlineDatetime },
  } = useField('outwardDeadlineDatetime');

  const {
    input: { value: returnDatetime },
  } = useField('returnDatetime');

  const {
    input: { value: marginDuration },
  } = useField('marginDuration');

  const formatDate = (date) => {
    if (typeof date !== 'string') {
      return new Date(date).toLocaleString();
    }

    // This is an ugly hack I known, but there's too much refactoring to do on the "SolidaryRegularAsk" / "SolidaryPunctualAsk" system
    // If the date is in string format (when not changed yet), we convert it to UTC
    return utcDateFormat(date, regularMode ? "HH':'mm':'ss" : "dd'/'MM'/'yyyy HH':'mm':'ss");
  };

  const formatHour = (date) => {
    return utcDateFormat(date, "HH':'mm'");
  };

  const formatOutwardDatetime = (date) => {
    return marginDuration > 0 ? utcDateFormat(date, "dd'/'MM'/'yyyy") : '';
  };

  const getMarginDate = () => {
    const hour = formatHour(outwardDatetime);
    if (marginDuration > 0) {
      if (hour === '10:30')
        return <p>{`Départ : ${formatOutwardDatetime(outwardDatetime)} 8h-13h `}</p>;
      if (hour === '14:30')
        return <p>{`Départ : ${formatOutwardDatetime(outwardDatetime)} 13h-18h `}</p>;
      if (hour === '18:30')
        return <p>{`Départ : ${formatOutwardDatetime(outwardDatetime)} 18h-21h `}</p>;
    }
    return <p>{`Départ : ${formatDate(outwardDatetime)} `}</p>;
  };

  if (regularMode) {
    return (
      <SolidaryQuestion question="Récapitulatif">
        {[
          outwardDatetime && getMarginDate(),
          returnDatetime && <p>{`Retour : ${formatDate(returnDatetime)} `}</p>,
          days && (
            <p>{`Jour : ${Object.keys(days)
              .filter((d) => !!days[d])
              .map((d) => translate(`custom.days.${d}`))
              .join(', ')} `}</p>
          ),
        ].filter((x) => x)}
      </SolidaryQuestion>
    );
  }

  return (
    <SolidaryQuestion question="Récapitulatif">
      {[
        outwardDatetime && getMarginDate(),
        outwardDeadlineDatetime && (
          <p>{`Départ limite : ${formatDate(outwardDeadlineDatetime)} `}</p>
        ),
        returnDatetime && <p>{`Retour : ${formatDate(returnDatetime)} `}</p>,
      ].filter((x) => x)}
    </SolidaryQuestion>
  );
};
