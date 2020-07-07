import React from 'react';
import { useTranslate, FormDataConsumer } from 'react-admin';
import EditIcon from '@material-ui/icons/Edit';

import { resolveVoluntaryAvailabilityHourRanges } from '../../SolidaryUserVolunteer/utils/resolveVoluntaryAvailabilityHourRanges';
import { AvailabilityRangeDialogButton } from '../../SolidaryUserVolunteer/Input/AvailabilityRangeDialogButton';

export const StructureTimeSlotsInput = (props) => {
  const translate = useTranslate();

  return (
    <FormDataConsumer>
      {({ formData }) => {
        const hourRanges = resolveVoluntaryAvailabilityHourRanges(formData);

        return (
          <div>
            <div>
              <strong>Créneaux horaires par défaut</strong>
            </div>
            <div>
              {translate(`custom.solidary_volunteers.edit.morning`)}
              {` (${hourRanges.morning || `6h-14h`})`}
              <AvailabilityRangeDialogButton label={<EditIcon />} source="m" {...props} />
            </div>
            <div>
              {translate(`custom.solidary_volunteers.edit.afternoon`)}
              {` (${hourRanges.afternoon || `12h-19h`})`}
              <AvailabilityRangeDialogButton label={<EditIcon />} source="a" {...props} />
            </div>
            <div>
              {translate(`custom.solidary_volunteers.edit.evening`)}
              {` (${hourRanges.evening || `17h-23h`})`}
              <AvailabilityRangeDialogButton label={<EditIcon />} source="e" {...props} />
            </div>
          </div>
        );
      }}
    </FormDataConsumer>
  );
};
