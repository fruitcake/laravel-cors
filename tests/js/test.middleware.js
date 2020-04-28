(function() {
  var CORS_SERVER;

  CORS_SERVER = 'localhost:9292';

  describe('CORS-INVALID', function() {
    return it('should allow access to invalid auth resource', function(done) {
      return fetch(`http://${CORS_SERVER}/protected`, {
        method: 'GET',
        mode: 'cors'
      }).then((response) => {
        expect(response.status).to.eql(401);
        return done();
      })
    });
  });

}).call(this);
