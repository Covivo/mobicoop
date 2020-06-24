import React, { useState } from 'react';
import { Dialog, DialogTitle, Grid, Slider, DialogContent, DialogActions } from '@material-ui/core';
import { useTranslate, useRefresh, SaveButton, useMutation } from 'react-admin';

const getProgressSteps = (translate) => [
  {
    percent: 25,
    label: translate('custom.solidaryAnimation.demandeTaking'),
  },
  {
    percent: 50,
    label: translate('custom.solidaryAnimation.searchSolution'),
  },
  {
    percent: 75,
    label: translate('custom.solidaryAnimation.carpoolTracking'),
  },
  {
    percent: 100,
    label: translate('custom.solidaryAnimation.askClosing'),
  },
];

export const SolidaryChangeProgressPopup = ({ solidary, onClose }) => {
  const translate = useTranslate();
  const [mutate, { loading }] = useMutation();
  const [currentIndex, setCurrentIndex] = useState(0);
  const refresh = useRefresh();
  const progressSteps = getProgressSteps(translate);

  const handleChange = (_, value) => {
    setCurrentIndex(value);
  };

  const handleSubmit = () => {
    mutate(
      {
        type: 'create',
        resource: 'solidary_animations',
        payload: {
          data: {
            actionName: 'solidary_update_progress_manually',
            progression: progressSteps[currentIndex].percent,
            // @TODO: Use id directly when dataprovider maps to id
            // I don't known why but actually deep object are not transformed
            user: `/users/${solidary.solidaryUser.user.id}`,
            solidary: solidary.id,
          },
        },
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

  const marks = progressSteps.map((step, index) => ({
    value: index,
    label: (
      <div style={{ textAlign: 'center' }}>
        {`${step.percent}%`}
        <br />
        {step.label}
      </div>
    ),
  }));

  return (
    <Dialog fullWidth maxWidth="md" open onClose={onClose}>
      <DialogTitle>{translate('custom.solidaryAnimation.addAction')}</DialogTitle>
      <DialogContent>
        <Grid container style={{ marginBottom: 50 }} justify="center" xs={12}>
          <Grid item xs={8}>
            <Slider
              value={currentIndex}
              onChangeCommitted={handleChange}
              step={1}
              max={marks.length - 1}
              marks={marks}
            />
          </Grid>
        </Grid>
        <DialogActions>
          <SaveButton handleSubmitWithRedirect={handleSubmit} disabled={loading} />
        </DialogActions>
      </DialogContent>
    </Dialog>
  );
};
