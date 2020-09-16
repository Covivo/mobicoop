import React from 'react';
import { Dialog, DialogTitle, Grid, DialogContent } from '@material-ui/core';

import {
  useTranslate,
  FormWithRedirect,
  ReferenceInput,
  SelectInput,
  SaveButton,
  required,
  useRefresh,
  useDataProvider,
  useMutation,
} from 'react-admin';

export const SolidaryAddDiaryPopup = ({ solidary, onClose }) => {
  const translate = useTranslate();
  const [mutate, { loading }] = useMutation();
  const dataProvider = useDataProvider();
  const refresh = useRefresh();

  const handleSubmit = async (content) => {
    const { data: action } = await dataProvider.getOne('actions', { id: content.action });

    const data = {
      actionName: action.name,
      solidary: solidary.id,
    };

    if (solidary && solidary.solidaryUser && solidary.solidaryUser.user) {
      data.user = `/users/${solidary.solidaryUser.user.id}`;
    }

    mutate(
      {
        type: 'create',
        resource: 'solidary_animations',
        payload: { data },
      },
      {
        onSuccess: () => {
          onClose();
          // We need to manually refresh the view
          // Because the API returns a badly formated object with id 99999
          // So it empty the list if we don't refresh
          refresh();
        },
      }
    );
  };

  return (
    <FormWithRedirect
      resource="posts"
      save={handleSubmit}
      render={({ handleSubmitWithRedirect, pristine, saving }) => (
        <Dialog fullWidth maxWidth="md" open onClose={onClose}>
          <DialogTitle>{translate('custom.solidaryAnimation.addAction')}</DialogTitle>
          <DialogContent>
            <Grid container xs={12}>
              <Grid item xs={6}>
                <ReferenceInput
                  label="Action"
                  validate={required()}
                  source="action"
                  reference="actions"
                  filterToQuery={() => ({ type: 'solidary' })}
                >
                  <SelectInput optionText={(o) => translate(`custom.actions.${o.name}`)} />
                </ReferenceInput>
              </Grid>
              {/* <Grid item xs={6}>
                <SelectInput
                  source="user"
                  style={{ width: 300 }}
                  label="Conducteur potentiel"
                  choices={(solidary.solutions || []).map((solution) => ({
                    id: solution.UserId,
                    name: solution.GivenName
                      ? `${solution.GivenName} ${solution.FamilyName}`
                      : 'Inconnu',
                  }))}
                />
              </Grid> */}
            </Grid>
            <SaveButton
              handleSubmitWithRedirect={handleSubmitWithRedirect}
              pristine={pristine}
              saving={saving}
              disabled={loading}
            />
          </DialogContent>
        </Dialog>
      )}
    />
  );
};
