import React, { useState } from 'react';
import { Edit, TabbedForm, FormTab, Toolbar, SaveButton } from 'react-admin';

import { AvailabilityGridInput } from './Input/AvailabilityGridInput';
import { ValidateCandidateInput } from './Input/ValidateCandidateInput';
import { UserInformationField } from './Fields/UserInformationField';
import { SolidaryVolunteerPlanningField } from './Fields/SolidaryVolunteerPlanningField';
import { SolidaryMessagesModal } from '../Solidary/SolidaryMessagesModal';

const SolidaryUserVolunteerEditToolbar = (props) => (
  <Toolbar {...props}>
    <SaveButton />
  </Toolbar>
);

export const SolidaryUserVolunteerEdit = (props) => {
  const [currentMessageSlot, setCurrentMessageSlot] = useState(null);

  const handleOpenSolidaryMessaging = (slot) => setCurrentMessageSlot(slot);
  const handleCloseSolidaryMessaging = () => setCurrentMessageSlot(null);

  return (
    <>
      <Edit {...props} title="Transporteurs Bénévoles > ajouter">
        <TabbedForm toolbar={<SolidaryUserVolunteerEditToolbar />}>
          <FormTab label="custom.solidary_volunteers.edit.availability">
            <UserInformationField />
            <AvailabilityGridInput />
          </FormTab>
          <FormTab label="custom.solidary_volunteers.edit.planning">
            <SolidaryVolunteerPlanningField onOpenMessaging={handleOpenSolidaryMessaging} />
          </FormTab>
          <FormTab label="custom.solidary_volunteers.edit.submission">
            <ValidateCandidateInput source="validatedCandidate" />
          </FormTab>
        </TabbedForm>
      </Edit>
      {currentMessageSlot && (
        <SolidaryMessagesModal {...currentMessageSlot} onClose={handleCloseSolidaryMessaging} />
      )}
    </>
  );
};
