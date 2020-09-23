import React from 'react';
import { FormDataConsumer, required, useTranslate } from 'react-admin';

import { DateInput, DateTimeInput } from 'react-admin-date-inputs';
import frLocale from 'date-fns/locale/fr';

const EventDuration = (props) => {
  const translate = useTranslate();

  return (
    <FormDataConsumer>
      {({ formData }) => {
        return formData.useTime ? (
          <>
            <DateTimeInput
              source="fromDate"
              label={translate('custom.label.event.dateTimeStart')}
              validate={[required()]}
              options={{ format: 'dd/MM/yyyy, HH:mm:ss', ampm: false, clearable: true }}
              providerOptions={{ locale: frLocale }}
            />
            <DateTimeInput
              source="toDate"
              label={translate('custom.label.event.dateTimeFin')}
              validate={[required()]}
              options={{ format: 'dd/MM/yyyy, HH:mm:ss', ampm: false, clearable: true }}
              providerOptions={{ locale: frLocale }}
            />
          </>
        ) : (
          <>
            <DateInput
              source="fromDate"
              label={translate('custom.label.event.dateStart')}
              validate={[required()]}
              options={{ format: 'dd/MM/yyyy' }}
              providerOptions={{ locale: frLocale }}
            />
            <DateInput
              source="toDate"
              label={translate('custom.label.event.dateFin')}
              validate={[required()]}
              options={{ format: 'dd/MM/yyyy' }}
              providerOptions={{ locale: frLocale }}
            />
          </>
        );
      }}
    </FormDataConsumer>
  );
};

export default EventDuration;
