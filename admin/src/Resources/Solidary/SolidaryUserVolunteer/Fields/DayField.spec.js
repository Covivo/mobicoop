import React from 'react';
import { render } from '@testing-library/react';

import { DayField } from './DayField';

describe('DayField', () => {
  it('should render only dashes if morning, afternoon and evening are false ', () => {
    const { getByText } = render(<DayField record={{}} source="any" />);
    expect(getByText('-/-/-')).toBeInTheDocument();
  });

  it('should render morning, afternoon or evening labels if truthy ', () => {
    const result = render(
      <DayField record={{ aMon: false, eMon: false, mMon: true }} source="Mon" />
    );

    expect(result.getByText('Mat./-/-')).toBeInTheDocument();

    result.rerender(<DayField record={{ aMon: true, eMon: false, mMon: false }} source="Mon" />);

    expect(result.getByText('-/Ap./-')).toBeInTheDocument();

    result.rerender(<DayField record={{ aMon: false, eMon: true, mMon: false }} source="Mon" />);

    expect(result.getByText('-/-/Soir')).toBeInTheDocument();
  });
});
