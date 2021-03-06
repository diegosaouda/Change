'use strict';

/* http://docs.angularjs.org/guide/dev_guide.e2e-testing */

describe('RBS Change', function() {

  beforeEach(function() {
    browser().navigateTo('../../');
  });


  it('should automatically redirect to /home when location hash/fragment is empty', function() {
    expect(browser().location().url()).toBe("/home");
  });

/*
  describe('view1', function() {

    beforeEach(function() {
      browser().navigateTo('/catalog');
    });


    it('should render view1 when user navigates to /view1', function() {
      expect(element('[ng-view] p:first').text()).
        toMatch(/partial for view 1/);
    });

  });


  describe('view2', function() {

    beforeEach(function() {
      browser().navigateTo('#/view2');
    });


    it('should render view2 when user navigates to /view2', function() {
      expect(element('[ng-view] p:first').text()).
        toMatch(/partial for view 2/);
    });

  });
*/
});
