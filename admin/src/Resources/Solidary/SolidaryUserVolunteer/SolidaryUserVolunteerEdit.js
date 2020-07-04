import React, { useState } from 'react';
import { Edit, TabbedForm, FormTab, Toolbar, SaveButton } from 'react-admin';

import { AvailabilityGridInput } from './Input/AvailabilityGridInput';
import { ValidateCandidateInput } from './Input/ValidateCandidateInput';
import { UserInformationField } from './Fields/UserInformationField';
import { SolidaryVolunteerPlanningField } from './Fields/SolidaryVolunteerPlanningField';
import { SolidaryMessagesModal } from '../Solidary/SolidaryMessagesModal';

// Because of <AvailabilityRangeDialogButton /> blur
// The pristine is set to true on modal close, so we force it here
// @TODO: Understand why the pristine status disappear
const EnabledSaveButton = (props) => <SaveButton {...props} pristine={false} />;
const SolidaryUserVolunteerEditToolbar = (props) => (
  <Toolbar {...props}>
    <EnabledSaveButton />
  </Toolbar>
);

export const SolidaryUserVolunteerEdit = (props) => {
  const [currentMessageSlot, setCurrentMessageSlot] = useState(null);

  const handleOpenSolidaryMessaging = (slot) => setCurrentMessageSlot(slot);
  const handleCloseSolidaryMessaging = () => setCurrentMessageSlot(null);

  return (
    <>
      <Edit {...props} title="Transporteurs bénévoles > ajouter">
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
