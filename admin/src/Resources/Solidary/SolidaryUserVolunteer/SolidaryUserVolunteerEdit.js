import React from 'react';
import { Edit, TabbedForm, FormTab, Toolbar, SaveButton } from 'react-admin';
import { Button, Dialog, DialogTitle, DialogContent } from '@material-ui/core';

import { AvailabilityGridInput } from './Input/AvailabilityGridInput';
import { ValidateCandidateInput } from './Input/ValidateCandidateInput';
import { UserInformationField } from './Fields/UserInformationField';
import { SolidaryVolunteerPlanningField } from './Fields/SolidaryVolunteerPlanningField';
import { AvailabilityRangeInput } from './Input/AvailabilityRangeInput';

const SolidaryUserVolunteerEditToolbar = (props) => (
  <Toolbar {...props}>
    <SaveButton />
  </Toolbar>
);

const AvailabilityRangeDialogInput = (props) => {
  const [open, setOpen] = React.useState(false);

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  return (
    <>
      <Dialog open={open} onClose={handleClose} aria-labelledby="form-dialog-title">
        <DialogTitle id="form-dialog-title">Subscribe</DialogTitle>
        <DialogContent>
          <AvailabilityRangeInput {...props} />
        </DialogContent>
      </Dialog>
      <Button onClick={handleOpen}>EDIT</Button>
    </>
  );
};

export const SolidaryUserVolunteerEdit = (props) => {
  return (
    <Edit {...props} title="Transporteurs Bénévoles > ajouter">
      <TabbedForm toolbar={<SolidaryUserVolunteerEditToolbar />}>
        <FormTab label="custom.solidary_volunteers.edit.availability">
          <UserInformationField />
          <AvailabilityGridInput />
        </FormTab>
        <FormTab label="custom.solidary_volunteers.edit.planning">
          <SolidaryVolunteerPlanningField />
        </FormTab>
        <FormTab label="custom.solidary_volunteers.edit.submission">
          <ValidateCandidateInput source="validatedCandidate" />
        </FormTab>
      </TabbedForm>
    </Edit>
  );
};
